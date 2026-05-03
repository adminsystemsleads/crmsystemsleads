<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Invoice;
use App\Models\InvoiceConfig;
use App\Models\InvoiceItem;
use App\Models\Pipeline;
use App\Services\GreenterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function index()
    {
        $team     = Auth::user()->currentTeam;
        $invoices = Invoice::where('team_id', $team->id)
            ->with(['deal', 'contact'])
            ->latest()
            ->paginate(30);

        return view('invoices.index', compact('invoices'));
    }

    public function create(Pipeline $pipeline, Deal $deal)
    {
        $team = Auth::user()->currentTeam;
        abort_unless($deal->team_id === $team->id, 403);

        $deal->load(['contact', 'dealProducts']);
        $config = InvoiceConfig::where('team_id', $team->id)->first();

        return view('invoices.create', compact('deal', 'pipeline', 'config'));
    }

    public function store(Request $request, Pipeline $pipeline, Deal $deal)
    {
        $team = Auth::user()->currentTeam;
        abort_unless($deal->team_id === $team->id, 403);

        $data = $request->validate([
            'tipo_doc'            => 'required|in:01,03',
            'fecha_emision'       => 'required|date',
            'fecha_vencimiento'   => 'nullable|date',
            'moneda'              => 'required|in:PEN,USD',
            'igv_porcentaje'      => 'required|numeric|min:0|max:100',
            'cliente_tipo_doc'    => 'required|string',
            'cliente_num_doc'     => 'required|string|max:15',
            'cliente_razon_social'=> 'required|string|max:250',
            'cliente_direccion'   => 'nullable|string|max:250',
            'observaciones'       => 'nullable|string|max:1000',
            'items'               => 'required|array|min:1',
            'items.*.descripcion' => 'required|string|max:250',
            'items.*.unidad'      => 'required|string|max:10',
            'items.*.cantidad'    => 'required|numeric|min:0.01',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.tip_afe_igv' => 'required|in:10,20,30',
            'items.*.cod_producto'=> 'nullable|string|max:50',
        ]);

        $config = InvoiceConfig::where('team_id', $team->id)->firstOrFail();

        $igvPct     = (float) $data['igv_porcentaje'];
        $serie      = $config->getSerie($data['tipo_doc']);
        $correlativo = $config->nextCorrelativo($data['tipo_doc']);

        // Calculate totals
        $opGravadas   = 0;
        $opExoneradas = 0;
        $opInafectas  = 0;
        $totalIgv     = 0;
        $items        = [];

        foreach ($data['items'] as $row) {
            $qty        = (float) $row['cantidad'];
            $precioUnit = (float) $row['precio_unitario'];
            $tipAfe     = $row['tip_afe_igv'];

            if ($tipAfe === '10') {
                // Gravado — precio incluye IGV
                $valorUnit = round($precioUnit / (1 + $igvPct / 100), 6);
                $igvItem   = round($valorUnit * $qty * $igvPct / 100, 2);
                $opGravadas += round($valorUnit * $qty, 2);
            } else {
                $valorUnit = $precioUnit;
                $igvItem   = 0;
                if ($tipAfe === '20') $opExoneradas += round($valorUnit * $qty, 2);
                else $opInafectas += round($valorUnit * $qty, 2);
            }

            $totalIgv  += $igvItem;
            $totalItem  = round($precioUnit * $qty, 2);

            $items[] = [
                'cod_producto'    => $row['cod_producto'] ?? 'ZZZ9999999AA',
                'descripcion'     => $row['descripcion'],
                'unidad'          => $row['unidad'],
                'cantidad'        => $qty,
                'precio_unitario' => $precioUnit,
                'valor_unitario'  => round($valorUnit, 6),
                'tip_afe_igv'     => $tipAfe,
                'igv_porcentaje'  => $igvPct,
                'igv'             => $igvItem,
                'total'           => $totalItem,
            ];
        }

        $totalIgv   = round($totalIgv, 2);
        $grandTotal = round($opGravadas + $opExoneradas + $opInafectas + $totalIgv, 2);

        $invoice = Invoice::create([
            'team_id'             => $team->id,
            'deal_id'             => $deal->id,
            'contact_id'          => $deal->contact_id,
            'tipo_doc'            => $data['tipo_doc'],
            'serie'               => $serie,
            'correlativo'         => $correlativo,
            'fecha_emision'       => $data['fecha_emision'],
            'fecha_vencimiento'   => $data['fecha_vencimiento'] ?? null,
            'moneda'              => $data['moneda'],
            'igv_porcentaje'      => $igvPct,
            'op_gravadas'         => $opGravadas,
            'op_exoneradas'       => $opExoneradas,
            'op_inafectas'        => $opInafectas,
            'igv'                 => $totalIgv,
            'total'               => $grandTotal,
            'cliente_tipo_doc'    => $data['cliente_tipo_doc'],
            'cliente_num_doc'     => $data['cliente_num_doc'],
            'cliente_razon_social'=> $data['cliente_razon_social'],
            'cliente_direccion'   => $data['cliente_direccion'] ?? null,
            'observaciones'       => $data['observaciones'] ?? null,
            'estado'              => 'draft',
        ]);

        foreach ($items as $item) {
            $invoice->items()->create($item);
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Comprobante creado correctamente.');
    }

    public function show(Invoice $invoice)
    {
        $team = Auth::user()->currentTeam;
        abort_unless($invoice->team_id === $team->id, 403);

        $invoice->load(['items', 'deal', 'contact']);
        $config = InvoiceConfig::where('team_id', $team->id)->first();

        return view('invoices.show', compact('invoice', 'config'));
    }

    public function print(Invoice $invoice)
    {
        $team = Auth::user()->currentTeam;
        abort_unless($invoice->team_id === $team->id, 403);

        $invoice->load(['items', 'deal', 'contact']);
        $config = InvoiceConfig::where('team_id', $team->id)->first();

        return view('invoices.print', compact('invoice', 'config'));
    }

    public function sendToSunat(Invoice $invoice, GreenterService $greenter)
    {
        $team = Auth::user()->currentTeam;
        abort_unless($invoice->team_id === $team->id, 403);
        abort_if(in_array($invoice->estado, ['accepted', 'cancelled']), 422);

        $config = InvoiceConfig::where('team_id', $team->id)->firstOrFail();

        try {
            $result = $greenter->sendToSunat($invoice, $config);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al comunicarse con SUNAT: ' . $e->getMessage());
        }

        $msg = $result['success']
            ? 'Comprobante aceptado por SUNAT.'
            : 'SUNAT rechazó el comprobante: ' . ($result['description'] ?? $result['message'] ?? '');

        return back()->with($result['success'] ? 'success' : 'error', $msg);
    }

    public function signOnly(Invoice $invoice, GreenterService $greenter)
    {
        $team = Auth::user()->currentTeam;
        abort_unless($invoice->team_id === $team->id, 403);

        $config = InvoiceConfig::where('team_id', $team->id)->firstOrFail();

        try {
            $greenter->buildAndSign($invoice, $config);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al firmar: ' . $e->getMessage());
        }

        return back()->with('success', 'Comprobante firmado y XML generado.');
    }

    public function downloadXml(Invoice $invoice)
    {
        $team = Auth::user()->currentTeam;
        abort_unless($invoice->team_id === $team->id, 403);
        abort_unless($invoice->xml_path && Storage::exists($invoice->xml_path), 404);

        return Storage::download($invoice->xml_path, "{$invoice->serie}-{$invoice->correlativo}.xml");
    }
}

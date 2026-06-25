<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\WhatsappAccount;
use App\Services\WhatsappTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    protected function currentTeam()
    {
        return Auth::user()->currentTeam;
    }

    public function index(Request $request)
    {
        $team   = $this->currentTeam();
        $teamId = $team->id;
        $teamTz = $team->effectiveTimezone();

        $q            = $request->query('q');
        $status       = $request->query('status');
        $createdFrom  = $request->query('created_from');
        $createdTo    = $request->query('created_to');
        $months       = array_values(array_filter((array) $request->query('months', [])));
        $responsibles = array_values(array_filter((array) $request->query('responsibles', [])));
        $stages       = array_values(array_filter((array) $request->query('stages', [])));
        $pipelines    = array_values(array_filter((array) $request->query('pipelines', [])));
        $cf           = (array) $request->query('cf', []);

        // Campos personalizados de contacto (para filtros).
        $contactFields = \App\Support\CustomFieldsHelper::fieldsFor($teamId, 'contact');

        $query = $this->buildFilteredQuery($request, $teamId, $teamTz, $contactFields);

        $contacts = $query->withCount('deals')->orderBy('name')->paginate(25)->withQueryString();
        $total    = $contacts->total();

        // Cuentas de WhatsApp activas (para el envío masivo de plantillas).
        $waAccounts = WhatsappAccount::where('team_id', $teamId)
            ->where('is_active', true)
            ->orderBy('name')->get(['id', 'name']);

        // Opciones para los filtros.
        $teamMembers   = $team->allUsers()->map(fn($u) => ['id' => $u->id, 'name' => $u->name])->values();
        $pipelinesList = Pipeline::where('team_id', $teamId)->orderBy('name')->get(['id', 'name']);
        $stagesList    = PipelineStage::whereIn('pipeline_id', $pipelinesList->pluck('id'))
            ->orderBy('pipeline_id')->orderBy('sort_order')->get(['id', 'name', 'pipeline_id']);

        // Meses con contactos creados (para el filtro de mes).
        $monthsList = Contact::where('team_id', $teamId)
            ->orderByDesc('created_at')->pluck('created_at')
            ->map(fn($d) => $d?->copy()->setTimezone($teamTz)->format('Y-m'))
            ->filter()->unique()->values();

        $filters = compact('createdFrom', 'createdTo', 'months', 'responsibles', 'stages', 'pipelines', 'cf');

        return view('contacts.index', compact(
            'contacts', 'q', 'status', 'filters', 'total', 'waAccounts',
            'teamMembers', 'pipelinesList', 'stagesList', 'monthsList', 'contactFields'
        ));
    }

    /**
     * Construye la query de contactos aplicando búsqueda + filtros del request.
     * Reutilizada por index() y por el envío masivo de plantillas.
     */
    protected function buildFilteredQuery(Request $request, int $teamId, $teamTz, $contactFields = null)
    {
        $contactFields = $contactFields ?? \App\Support\CustomFieldsHelper::fieldsFor($teamId, 'contact');

        $q            = $request->query('q');
        $status       = $request->query('status');
        $createdFrom  = $request->query('created_from');
        $createdTo    = $request->query('created_to');
        $months       = array_values(array_filter((array) $request->query('months', [])));
        $responsibles = array_values(array_filter((array) $request->query('responsibles', [])));
        $stages       = array_values(array_filter((array) $request->query('stages', [])));
        $pipelines    = array_values(array_filter((array) $request->query('pipelines', [])));
        $cf           = (array) $request->query('cf', []);

        $query = Contact::where('team_id', $teamId)
            ->when($q, fn($query) => $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%")
                   ->orWhere('phone', 'like', "%{$q}%")
                   ->orWhere('company', 'like', "%{$q}%")
                   ->orWhereHas('deals', fn($d) => $d->where('title', 'like', "%{$q}%"));
            }))
            ->when($status, fn($query) => $query->where('status', $status))
            ->when($createdFrom, fn($query) => $query->where('created_at', '>=', Carbon::parse($createdFrom, $teamTz)->startOfDay()->utc()))
            ->when($createdTo, fn($query) => $query->where('created_at', '<=', Carbon::parse($createdTo, $teamTz)->endOfDay()->utc()))
            ->when($months, fn($query) => $query->where(function ($w) use ($months, $teamTz) {
                foreach ($months as $m) {
                    try { $start = Carbon::createFromFormat('Y-m-d', $m . '-01', $teamTz)->startOfMonth(); }
                    catch (\Throwable $e) { continue; }
                    $w->orWhereBetween('created_at', [$start->copy()->utc(), $start->copy()->endOfMonth()->utc()]);
                }
            }))
            ->when($responsibles, fn($query) => $query->whereHas('deals', fn($d) => $d->whereIn('responsible_id', $responsibles)))
            ->when($stages, fn($query) => $query->whereHas('deals', fn($d) => $d->whereIn('stage_id', $stages)))
            ->when($pipelines, fn($query) => $query->whereHas('deals', fn($d) => $d->whereIn('pipeline_id', $pipelines)));

        foreach ($contactFields as $field) {
            $val  = $cf[$field->id] ?? null;
            $vals = array_values(array_filter(is_array($val) ? $val : [$val], fn($v) => $v !== null && $v !== ''));
            if (empty($vals)) continue;

            $query->whereHas('customFieldValues', function ($v) use ($field, $vals) {
                $v->where('custom_field_id', $field->id)
                  ->where(function ($w) use ($field, $vals) {
                      foreach ($vals as $vv) {
                          if ($field->field_type === 'multiselect') {
                              $w->orWhere('value', 'like', '%"' . $vv . '"%');
                          } elseif ($field->field_type === 'select') {
                              $w->orWhere('value', $vv);
                          } else {
                              $w->orWhere('value', 'like', '%' . $vv . '%');
                          }
                      }
                  });
            });
        }

        return $query;
    }

    /**
     * Envío masivo de una plantilla de WhatsApp a los contactos filtrados.
     * Procesa por lotes (offset/limit) para reportar progreso desde el frontend.
     */
    public function bulkSendTemplate(Request $request, WhatsappTemplateService $service)
    {
        $team   = $this->currentTeam();
        $teamId = $team->id;
        $teamTz = $team->effectiveTimezone();

        $data = $request->validate([
            'account_id' => 'required|integer',
            'template'   => 'required|string|max:512',
            'language'   => 'required|string|max:10',
            'vars'          => 'nullable|array',
            'vars.*'        => 'nullable|string|max:1000',
            'header_format' => 'nullable|in:IMAGE,VIDEO,DOCUMENT',
            'header_media'  => 'nullable|string|max:2000',
            'offset'        => 'required|integer|min:0',
            'limit'         => 'required|integer|min:1|max:50',
        ]);

        $account = WhatsappAccount::where('team_id', $teamId)->findOrFail($data['account_id']);

        $headerMedia = null;
        if (!empty($data['header_format']) && !empty($data['header_media'])) {
            // Re-subir el archivo al número para obtener un media id (entrega fiable).
            $mid = $service->uploadMediaFromUrl($account, $data['header_media'], $data['header_format']);
            $headerMedia = $mid['ok']
                ? ['format' => $data['header_format'], 'id' => $mid['id']]
                : ['format' => $data['header_format'], 'link' => $data['header_media']];
        }

        // Solo contactos con teléfono, orden estable por id.
        $base = $this->buildFilteredQuery($request, $teamId, $teamTz)
            ->whereNotNull('phone')->where('phone', '!=', '')
            ->orderBy('id');

        $total = (clone $base)->count();

        $batch = $base->offset($data['offset'])->limit($data['limit'])->get(['id', 'name', 'phone']);

        $vars   = array_values($data['vars'] ?? []);
        $sent   = 0;
        $failed = 0;
        $errors = [];

        foreach ($batch as $c) {
            $phone = preg_replace('/\D+/', '', (string) $c->phone);
            if ($phone === '') { $failed++; continue; }

            // Variables del cuerpo: {nombre} → nombre del contacto.
            $bodyParams = array_map(function ($v) use ($c) {
                return str_ireplace('{nombre}', (string) $c->name, (string) $v);
            }, $vars);

            $res = $service->sendTemplate($account, $phone, $data['template'], $data['language'], $bodyParams, [], $headerMedia);

            if ($res['ok'] ?? false) {
                $sent++;
            } else {
                $failed++;
                if (count($errors) < 3) {
                    $errors[] = $c->name . ': ' . ($res['message'] ?? 'error');
                }
            }
        }

        $processed = $data['offset'] + $batch->count();

        return response()->json([
            'ok'        => true,
            'total'     => $total,
            'processed' => $processed,
            'sent'      => $sent,
            'failed'    => $failed,
            'done'      => $processed >= $total || $batch->count() === 0,
            'errors'    => $errors,
        ]);
    }

    public function create()
    {
        $customFields = \App\Support\CustomFieldsHelper::fieldsFor($this->currentTeam()->id, 'contact');
        return view('contacts.edit', ['contact' => null, 'customFields' => $customFields, 'customValues' => []]);
    }

    public function store(Request $request)
    {
        $team = $this->currentTeam();

        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'email'      => 'nullable|email|max:255',
            'phone'      => 'nullable|string|max:50',
            'company'    => 'nullable|string|max:255',
            'position'   => 'nullable|string|max:255',
            'status'     => 'nullable|string|max:50',
            'source'     => 'nullable|string|max:100',
            'notes'      => 'nullable|string',
            'custom_fields' => 'nullable|array',
        ]);

        $data['name']     = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        $data['team_id']  = $team->id;
        $data['owner_id'] = Auth::id();
        $data['status']   = $data['status'] ?? 'nuevo';

        $customFieldsPayload = $data['custom_fields'] ?? null;
        unset($data['custom_fields']);

        $contact = Contact::create($data);

        \App\Support\CustomFieldsHelper::sync($contact, $customFieldsPayload, $team->id, 'contact');

        return redirect()->route('contacts.index')->with('status', 'Contacto creado correctamente.');
    }

    public function edit(Contact $contact)
    {
        $team = $this->currentTeam();
        abort_unless($contact->team_id === $team->id, 404);

        $contact->load(['deals.pipeline', 'deals.stage', 'owner']);

        $customFields = \App\Support\CustomFieldsHelper::fieldsFor($team->id, 'contact');
        $customValues = \App\Support\CustomFieldsHelper::valuesFor($contact);

        return view('contacts.edit', compact('contact', 'customFields', 'customValues'));
    }

    public function update(Request $request, Contact $contact)
    {
        $team = $this->currentTeam();
        abort_unless($contact->team_id === $team->id, 404);

        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'email'      => 'nullable|email|max:255',
            'phone'      => 'nullable|string|max:50',
            'company'    => 'nullable|string|max:255',
            'position'   => 'nullable|string|max:255',
            'status'     => 'nullable|string|max:50',
            'source'     => 'nullable|string|max:100',
            'notes'      => 'nullable|string',
            'custom_fields' => 'nullable|array',
        ]);

        $data['name'] = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));

        $customFieldsPayload = $data['custom_fields'] ?? null;
        unset($data['custom_fields']);

        $contact->update($data);

        \App\Support\CustomFieldsHelper::sync($contact, $customFieldsPayload, $team->id, 'contact');

        // Permanece en la misma ficha de edición tras guardar (no cierra la vista)
        return redirect()->route('contacts.edit', $contact)
            ->with('status', 'Contacto actualizado correctamente.');
    }

    public function destroy(Contact $contact)
    {
        $team = $this->currentTeam();
        abort_unless($contact->team_id === $team->id, 404);

        $contact->delete();

        return redirect()->route('contacts.index')->with('status', 'Contacto eliminado.');
    }

    /* ============ EXPORT CSV ============ */

    public function export(Request $request)
    {
        $team   = $this->currentTeam();
        $q      = $request->query('q');
        $status = $request->query('status');

        // Query SIN ejecutar — la cursor() corre en el closure
        $query = Contact::where('team_id', $team->id)
            ->when($q, fn($query) => $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%")
                   ->orWhere('phone', 'like', "%{$q}%")
                   ->orWhere('company', 'like', "%{$q}%");
            }))
            ->when($status, fn($query) => $query->where('status', $status))
            ->orderBy('id'); // ordenar por id es más eficiente con cursor

        $filename = 'contactos_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'              => 'no-cache',
            'X-Accel-Buffering'   => 'no',
        ];

        return response()->streamDownload(function () use ($query) {
            @set_time_limit(0);
            while (ob_get_level() > 0) { ob_end_clean(); }

            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM Excel

            fputcsv($out, [
                'id', 'first_name', 'last_name', 'name', 'email', 'phone',
                'company', 'position', 'tipo_doc', 'num_doc', 'razon_social',
                'status', 'source', 'notes', 'created_at',
            ]);
            flush();

            $count = 0;
            foreach ($query->cursor() as $c) {
                fputcsv($out, [
                    (string) $c->id,
                    (string) ($c->first_name ?? ''),
                    (string) ($c->last_name ?? ''),
                    (string) ($c->name ?? ''),
                    (string) ($c->email ?? ''),
                    (string) ($c->phone ?? ''),
                    (string) ($c->company ?? ''),
                    (string) ($c->position ?? ''),
                    (string) ($c->tipo_doc ?? ''),
                    (string) ($c->num_doc ?? ''),
                    (string) ($c->razon_social ?? ''),
                    (string) ($c->status ?? ''),
                    (string) ($c->source ?? ''),
                    str_replace(["\r", "\n"], ' ', (string) ($c->notes ?? '')),
                    $c->created_at?->format('Y-m-d H:i:s') ?? '',
                ]);

                if (++$count % 200 === 0) {
                    flush();
                }
            }

            fclose($out);
            flush();
        }, $filename, $headers);
    }

    /* ============ IMPORT CSV ============ */

    public function importForm()
    {
        return view('contacts.import');
    }

    public function importTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_contactos.csv"',
        ];

        return response()->stream(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM para Excel
            fputcsv($out, ['first_name', 'last_name', 'email', 'phone', 'company', 'position', 'tipo_doc', 'num_doc', 'status', 'source', 'notes']);
            fputcsv($out, ['Juan', 'Pérez', 'juan@empresa.com', '+51987654321', 'Empresa SAC', 'Gerente', '6', '20123456789', 'nuevo', 'web', 'Cliente referido']);
            fputcsv($out, ['María', 'Gómez', 'maria@correo.com', '+51912345678', '', '', '1', '12345678', 'activo', 'whatsapp', '']);
            fputcsv($out, ['Carlos', '', '', '+51900111222', '', '', '', '', 'nuevo', 'manual', '']);
            fclose($out);
        }, 200, $headers);
    }

    public function importStore(Request $request)
    {
        $team = $this->currentTeam();

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return back()->with('error', 'No se pudo abrir el archivo.');
        }

        $first = fread($handle, 3);
        if ($first !== "\xEF\xBB\xBF") rewind($handle);

        $sample = fgets($handle);
        $delimiter = (substr_count($sample, ';') > substr_count($sample, ',')) ? ';' : ',';
        rewind($handle);
        if ($first === "\xEF\xBB\xBF") fread($handle, 3);

        $headerRow = fgetcsv($handle, 0, $delimiter);
        if (!$headerRow) {
            fclose($handle);
            return back()->with('error', 'CSV vacío o inválido.');
        }
        $headers = array_map(fn($h) => strtolower(trim($h)), $headerRow);

        $created = 0;
        $errors  = [];
        $line    = 1;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line++;
            if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) === 0) continue;

            $row = array_pad($row, count($headers), null);
            $assoc = array_combine($headers, $row);

            $firstName = trim($assoc['first_name'] ?? '');
            if ($firstName === '') {
                $errors[] = "Línea {$line}: falta first_name.";
                continue;
            }
            $lastName = trim($assoc['last_name'] ?? '');
            $email    = trim($assoc['email'] ?? '');
            if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Línea {$line}: email inválido ({$email}).";
                continue;
            }

            Contact::create([
                'team_id'      => $team->id,
                'owner_id'     => Auth::id(),
                'first_name'   => $firstName,
                'last_name'    => $lastName ?: null,
                'name'         => trim($firstName . ' ' . $lastName),
                'email'        => $email ?: null,
                'phone'        => trim($assoc['phone'] ?? '') ?: null,
                'company'      => trim($assoc['company'] ?? '') ?: null,
                'position'     => trim($assoc['position'] ?? '') ?: null,
                'tipo_doc'     => trim($assoc['tipo_doc'] ?? '') ?: null,
                'num_doc'      => trim($assoc['num_doc'] ?? '') ?: null,
                'status'       => trim($assoc['status'] ?? 'nuevo') ?: 'nuevo',
                'source'       => trim($assoc['source'] ?? '') ?: null,
                'notes'        => trim($assoc['notes'] ?? '') ?: null,
            ]);
            $created++;
        }
        fclose($handle);

        $msg = "Se importaron {$created} contactos.";
        if ($errors) {
            $msg .= ' Errores: ' . implode(' | ', array_slice($errors, 0, 5));
        }

        return redirect()->route('contacts.index')->with('status', $msg);
    }
}

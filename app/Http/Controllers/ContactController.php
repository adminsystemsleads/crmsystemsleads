<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
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
        $q      = $request->query('q');
        $status = $request->query('status');

        $contacts = Contact::where('team_id', $team->id)
            ->when($q, fn($query) => $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%")
                   ->orWhere('phone', 'like', "%{$q}%")
                   ->orWhere('company', 'like', "%{$q}%");
            }))
            ->when($status, fn($query) => $query->where('status', $status))
            ->withCount('deals')
            ->orderBy('name')
            ->paginate(25);

        return view('contacts.index', compact('contacts', 'q', 'status'));
    }

    public function create()
    {
        return view('contacts.edit', ['contact' => null]);
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
        ]);

        $data['name']     = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        $data['team_id']  = $team->id;
        $data['owner_id'] = Auth::id();
        $data['status']   = $data['status'] ?? 'nuevo';

        Contact::create($data);

        return redirect()->route('contacts.index')->with('status', 'Contacto creado correctamente.');
    }

    public function edit(Contact $contact)
    {
        $team = $this->currentTeam();
        abort_unless($contact->team_id === $team->id, 404);

        $contact->load(['deals.pipeline', 'deals.stage', 'owner']);

        return view('contacts.edit', compact('contact'));
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
        ]);

        $data['name'] = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));

        $contact->update($data);

        return back()->with('status', 'Contacto actualizado correctamente.');
    }

    public function destroy(Contact $contact)
    {
        $team = $this->currentTeam();
        abort_unless($contact->team_id === $team->id, 404);

        $contact->delete();

        return redirect()->route('contacts.index')->with('status', 'Contacto eliminado.');
    }

    /* ============ EXPORT CSV ============ */

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $team   = $this->currentTeam();
        $q      = $request->query('q');
        $status = $request->query('status');

        $contacts = Contact::where('team_id', $team->id)
            ->when($q, fn($query) => $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%")
                   ->orWhere('phone', 'like', "%{$q}%")
                   ->orWhere('company', 'like', "%{$q}%");
            }))
            ->when($status, fn($query) => $query->where('status', $status))
            ->orderBy('name')
            ->get();

        $filename = 'contactos_' . now()->format('Y-m-d_His') . '.csv';

        return response()->stream(function () use ($contacts) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM para Excel

            fputcsv($out, [
                'id', 'first_name', 'last_name', 'name', 'email', 'phone',
                'company', 'position', 'tipo_doc', 'num_doc', 'razon_social',
                'status', 'source', 'notes', 'created_at',
            ]);

            foreach ($contacts as $c) {
                fputcsv($out, [
                    $c->id,
                    $c->first_name,
                    $c->last_name,
                    $c->name,
                    $c->email,
                    $c->phone,
                    $c->company,
                    $c->position,
                    $c->tipo_doc,
                    $c->num_doc,
                    $c->razon_social,
                    $c->status,
                    $c->source,
                    str_replace(["\r", "\n"], ' ', (string) $c->notes),
                    $c->created_at?->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($out);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
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

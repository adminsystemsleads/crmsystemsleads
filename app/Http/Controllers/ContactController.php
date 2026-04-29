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
}

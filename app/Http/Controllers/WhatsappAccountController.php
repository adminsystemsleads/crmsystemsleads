<?php

namespace App\Http\Controllers;

use App\Models\Pipeline;
use App\Models\WhatsappAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class WhatsappAccountController extends Controller
{
    protected function currentTeam()
    {
        return Auth::user()->currentTeam;
    }

    public function index()
    {
        $team = $this->currentTeam();

        $accounts = WhatsappAccount::where('team_id', $team->id)
            ->with('pipeline')
            ->orderBy('id', 'desc')
            ->get();

        return view('whatsapp.accounts.index', compact('accounts'));
    }

    protected function teamMembers($team)
    {
        return $team
            ? $team->allUsers()->sortBy('name')->values()
            : collect();
    }

    public function create()
    {
        $team = $this->currentTeam();

        $pipelines   = Pipeline::where('team_id', $team->id)->orderBy('sort_order')->get();
        $teamMembers = $this->teamMembers($team);

        return view('whatsapp.accounts.create', compact('pipelines', 'teamMembers'));
    }

    public function store(Request $request)
    {
        $team = $this->currentTeam();

        $teamUserIds = $this->teamMembers($team)->pluck('id')->all();

        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'phone_number_id' => 'required|string|max:255',
            'waba_id'         => 'nullable|string|max:255',
            'business_id'     => 'nullable|string|max:255',
            'access_token'    => 'required|string',
            'verify_token'    => 'required|string|max:255',
            'pipeline_id'     => 'required|exists:pipelines,id',
            'is_active'       => 'nullable|boolean',
            'assignee_ids'    => 'nullable|array',
            'assignee_ids.*'  => ['integer', \Illuminate\Validation\Rule::in($teamUserIds)],
        ]);

        // asegurar pipeline del team
        Pipeline::where('team_id', $team->id)->where('id', $data['pipeline_id'])->firstOrFail();

        $account = WhatsappAccount::create([
            'team_id'         => $team->id,
            'name'            => $data['name'],
            'phone_number_id' => $data['phone_number_id'],
            'waba_id'         => $data['waba_id'] ?? null,
            'business_id'     => $data['business_id'] ?? null,
            'access_token'    => $data['access_token'],
            'verify_token'    => $data['verify_token'],
            'pipeline_id'     => $data['pipeline_id'],
            'is_active'       => $request->boolean('is_active', true),
        ]);

        $account->assignees()->sync($data['assignee_ids'] ?? []);

        return redirect()->route('whatsapp.accounts.index')->with('status', 'Cuenta de WhatsApp agregada.');
    }

    public function edit(WhatsappAccount $account)
    {
        $team = $this->currentTeam();

        abort_unless($account->team_id === $team->id, 404);

        $pipelines      = Pipeline::where('team_id', $team->id)->orderBy('sort_order')->get();
        $teamMembers    = $this->teamMembers($team);
        $assignedUserIds = $account->assignees()->pluck('users.id')->all();

        return view('whatsapp.accounts.edit', compact('account', 'pipelines', 'teamMembers', 'assignedUserIds'));
    }

    public function update(Request $request, WhatsappAccount $account)
    {
        $team = $this->currentTeam();
        abort_unless($account->team_id === $team->id, 404);

        $teamUserIds = $this->teamMembers($team)->pluck('id')->all();

        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'phone_number_id' => 'required|string|max:255',
            'waba_id'         => 'nullable|string|max:255',
            'business_id'     => 'nullable|string|max:255',
            'access_token'    => 'required|string',
            'verify_token'    => 'required|string|max:255',
            'pipeline_id'     => 'required|exists:pipelines,id',
            'is_active'       => 'nullable|boolean',
            'assignee_ids'    => 'nullable|array',
            'assignee_ids.*'  => ['integer', \Illuminate\Validation\Rule::in($teamUserIds)],
        ]);

        Pipeline::where('team_id', $team->id)->where('id', $data['pipeline_id'])->firstOrFail();

        $account->update([
            'name'            => $data['name'],
            'phone_number_id' => $data['phone_number_id'],
            'waba_id'         => $data['waba_id'] ?? null,
            'business_id'     => $data['business_id'] ?? null,
            'access_token'    => $data['access_token'],
            'verify_token'    => $data['verify_token'],
            'pipeline_id'     => $data['pipeline_id'],
            'is_active'       => $request->boolean('is_active', true),
        ]);

        $account->assignees()->sync($data['assignee_ids'] ?? []);

        return redirect()->route('whatsapp.accounts.index')->with('status', 'Cuenta actualizada.');
    }

    /**
     * Detecta automáticamente el WABA ID desde el Phone Number ID + access token.
     * Se llama por AJAX desde el formulario.
     */
    public function detectWabaId(Request $request)
    {
        $data = $request->validate([
            'phone_number_id' => 'required|string|max:50',
            'access_token'    => 'required|string',
        ]);

        try {
            $res = Http::withToken($data['access_token'])
                ->acceptJson()
                ->timeout(15)
                ->get("https://graph.facebook.com/v20.0/{$data['phone_number_id']}", [
                    'fields' => 'whatsapp_business_account_id,verified_name,display_phone_number',
                ]);

            $body = $res->json() ?? [];
            if (!$res->successful()) {
                return response()->json([
                    'ok'      => false,
                    'message' => $body['error']['message'] ?? 'Error al consultar Meta',
                ], 422);
            }

            $wabaId = $body['whatsapp_business_account_id'] ?? null;
            if (!$wabaId) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'No se encontró el WABA ID. Verifica que el token tenga permiso whatsapp_business_management.',
                ], 422);
            }

            return response()->json([
                'ok'                    => true,
                'waba_id'               => $wabaId,
                'verified_name'         => $body['verified_name'] ?? null,
                'display_phone_number'  => $body['display_phone_number'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(WhatsappAccount $account)
    {
        $team = $this->currentTeam();
        abort_unless($account->team_id === $team->id, 404);

        $account->delete();

        return back()->with('status', 'Cuenta eliminada.');
    }
}

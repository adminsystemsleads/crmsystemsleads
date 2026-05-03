<?php 

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Contact;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; // 👈 IMPORTANTE para la validación fuerte
use Illuminate\Support\Facades\Gate;
class DealController extends Controller
{
    protected function currentTeam()
    {
        return Auth::user()->currentTeam; // Jetstream Teams
    }

    protected function pipelineForTeam($id): Pipeline
    {
        $team = $this->currentTeam();

        return Pipeline::where('team_id', $team->id)
            ->where('id', $id)
            ->firstOrFail();
    }

    protected function dealForPipeline(Pipeline $pipeline, Deal $deal): Deal
    {
        abort_unless(
            $deal->team_id === $pipeline->team_id &&
            $deal->pipeline_id === $pipeline->id,
            404
        );

        return $deal;
    }

    /* ============ 
     *   CREATE
     * ============*/

    public function create(Pipeline $pipeline, Request $request)
    {
        $pipeline = $this->pipelineForTeam($pipeline->id);
        $team     = $this->currentTeam();
        $user     = Auth::user();

        // Contactos del team
        $contacts = Contact::where('team_id', $team->id)
            ->orderBy('name')
            ->get();

        // Fases del pipeline
        $stages = $pipeline->stages()
            ->orderBy('sort_order')
            ->get();

        // Miembros del team (para Persona responsable)
        // 👉 usamos allUsers() para incluir owner + admins + miembros
        $teamMembers = $team
            ? $team->allUsers()->sortBy('name')->values()
            : collect();

        // Fase por defecto (viene desde el kanban)
        $defaultStageId = $request->query('stage');

        return view('deals.create', compact(
            'pipeline',
            'contacts',
            'stages',
            'defaultStageId',
            'teamMembers'
        ));
    }

    public function store(Request $request, Pipeline $pipeline)
    {
        $pipeline = $this->pipelineForTeam($pipeline->id);
        $team     = $this->currentTeam();
        $user     = Auth::user();

        // IDs válidos de usuarios del team (owner + admins + miembros)
        $teamUserIds = $team
            ? $team->allUsers()->pluck('id')->toArray()
            : [];

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'amount'      => 'nullable|numeric',
            'currency'    => 'nullable|string|size:3',
            'contact_id'  => 'nullable|exists:contacts,id',

            // NUEVOS CAMPOS PARA CREAR CONTACTO
            'new_contact_name'  => 'nullable|string|max:255',
            'new_contact_email' => 'nullable|email|max:255',
            'new_contact_phone' => 'nullable|string|max:50',

            'stage_id'    => 'required|exists:pipeline_stages,id',
            'close_date'  => 'nullable|date',
            'description' => 'nullable|string',

            // Persona responsable: debe pertenecer al team
            'responsible_id' => [
                'nullable',
                'integer',
                Rule::in($teamUserIds),
            ],
        ]);

        // Si el usuario llenó "Nuevo contacto", creamos el contacto y lo usamos
        $contactId = $data['contact_id'] ?? null;

        if (
            !empty($data['new_contact_name']) ||
            !empty($data['new_contact_email']) ||
            !empty($data['new_contact_phone'])
        ) {
            $contact = Contact::create([
                'team_id'    => $team->id,
                'owner_id'   => $user->id,
                'first_name' => $data['new_contact_name'],
                'last_name'  => null,
                'name'       => $data['new_contact_name']
                                ?? ($data['new_contact_email'] ?? 'Contacto sin nombre'),
                'email'      => $data['new_contact_email'] ?? null,
                'phone'      => $data['new_contact_phone'] ?? null,
                'company'    => null,
                'position'   => null,
                'status'     => 'nuevo',
                'source'     => 'crm',
                'notes'      => null,
            ]);

            $contactId = $contact->id;
        }

        $stage = PipelineStage::where('pipeline_id', $pipeline->id)
            ->where('id', $data['stage_id'])
            ->firstOrFail();

        $status = 'open';
        if ($stage->is_won)  $status = 'won';
        if ($stage->is_lost) $status = 'lost';

        Deal::create([
            'team_id'        => $team->id,
            'owner_id'       => $user->id,
            'pipeline_id'    => $pipeline->id,
            'stage_id'       => $stage->id,
            'contact_id'     => $contactId,
            'responsible_id' => $data['responsible_id'] ?? null,
            'title'          => $data['title'],
            'amount'         => $data['amount'] ?? null,
            'currency'       => $data['currency'] ?? 'PEN',
            'status'         => $status,
            'close_date'     => $data['close_date'] ?? null,
            'description'    => $data['description'] ?? null,
        ]);

        return redirect()->route('pipelines.kanban', $pipeline)
            ->with('status', 'Negociación creada correctamente.');
    }

    /* ============ 
     *   SHOW
     * ============*/

    public function show(Pipeline $pipeline, Deal $deal)
    {
        $pipeline = $this->pipelineForTeam($pipeline->id);
        $deal     = $this->dealForPipeline($pipeline, $deal)->load('contact', 'stage');
        Gate::authorize('view', $pipeline);
        return view('deals.show', compact('pipeline', 'deal'));
    }

    /* ============ 
     *   EDIT / UPDATE
     * ============*/

    public function edit(Pipeline $pipeline, Deal $deal)
    {
        $pipeline = $this->pipelineForTeam($pipeline->id);
        $deal     = $this->dealForPipeline($pipeline, $deal);

         Gate::authorize('edit', $pipeline);
        $user = auth()->user();
        $team = $user->currentTeam ?? null;

        // Fases del pipeline
        $stages = $pipeline->stages()
            ->orderBy('sort_order')
            ->get();

        // Contactos del team actual
        $contactsQuery = Contact::query();
        if ($team) {
            $contactsQuery->where('team_id', $team->id);
        }
        $contacts = $contactsQuery
            ->orderBy('name')
            ->get();

        // Miembros del team (para Persona responsable)
        // 👉 usamos allUsers() para incluir owner + admins + miembros
        $teamMembers = $team
            ? $team->allUsers()->sortBy('name')->values()
            : collect();

        // Cargar relaciones para comentarios, actividades y conversaciones WA
        $deal->load([
            'contact',
            'comments.user',
            'activities.user',
            'whatsappConversations.account',
            'dealProducts',
        ]);

        $comments              = $deal->comments;
        $activities            = $deal->activities;
        $whatsappConversations = $deal->whatsappConversations;
        $dealProducts          = $deal->dealProducts;

        $catalogProducts = \App\Models\Product::where('team_id', $deal->team_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'unit', 'currency']);

        return view('deals.edit', compact(
            'pipeline',
            'deal',
            'stages',
            'contacts',
            'teamMembers',
            'comments',
            'activities',
            'whatsappConversations',
            'dealProducts',
            'catalogProducts'
        ));
    }

    public function update(Request $request, Pipeline $pipeline, Deal $deal)
    {
        $pipeline = $this->pipelineForTeam($pipeline->id);
        $deal     = $this->dealForPipeline($pipeline, $deal);
        $team     = $this->currentTeam();
        Gate::authorize('edit', $pipeline);
        // IDs válidos de usuarios del team
        $teamUserIds = $team
            ? $team->allUsers()->pluck('id')->toArray()
            : [];

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'amount'      => 'nullable|numeric',
            'currency'    => 'nullable|string|size:3',
            'contact_id'  => 'nullable|exists:contacts,id',
            'stage_id'    => 'required|exists:pipeline_stages,id',
            'close_date'  => 'nullable|date',
            'description' => 'nullable|string',

            // Persona responsable: debe pertenecer al team
            'responsible_id' => [
                'nullable',
                'integer',
                Rule::in($teamUserIds),
            ],
        ]);

        $stage = PipelineStage::where('pipeline_id', $pipeline->id)
            ->where('id', $data['stage_id'])
            ->firstOrFail();

        $status = 'open';
        if ($stage->is_won)  $status = 'won';
        if ($stage->is_lost) $status = 'lost';

        $deal->update([
            'contact_id'     => $data['contact_id'] ?? null,
            'responsible_id' => $data['responsible_id'] ?? null,
            'title'          => $data['title'],
            'amount'         => $data['amount'] ?? null,
            'currency'       => $data['currency'] ?? $deal->currency,
            'stage_id'       => $stage->id,
            'status'         => $status,
            'close_date'     => $data['close_date'] ?? null,
            'description'    => $data['description'] ?? null,
        ]);

        return redirect()->route('pipelines.kanban', $pipeline)
            ->with('status', 'Negociación actualizada.');
    }

    /* ============ 
     *   DELETE
     * ============*/

    public function destroy(Pipeline $pipeline, Deal $deal)
    {
        $pipeline = $this->pipelineForTeam($pipeline->id);
        $deal     = $this->dealForPipeline($pipeline, $deal);
        Gate::authorize('delete', $pipeline);
        $deal->delete();

        return back()->with('status', 'Negociación eliminada.');
    }

    /* ============ 
     *   MOVE STAGE (desde Kanban)
     * ============*/

    public function move(Request $request, Pipeline $pipeline, Deal $deal)
    {
        $pipeline = $this->pipelineForTeam($pipeline->id);
        $deal     = $this->dealForPipeline($pipeline, $deal);
        Gate::authorize('edit', $pipeline);
        $data = $request->validate([
            'stage_id' => 'required|exists:pipeline_stages,id',
        ]);

        $stage = PipelineStage::where('pipeline_id', $pipeline->id)
            ->where('id', $data['stage_id'])
            ->firstOrFail();

        $status = 'open';
        if ($stage->is_won)  $status = 'won';
        if ($stage->is_lost) $status = 'lost';

        $deal->update([
            'stage_id' => $stage->id,
            'status'   => $status,
        ]);

        // 🔹 Si viene por AJAX (accept: application/json), respondemos JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success'  => true,
                'stage_id' => $stage->id,
                'status'   => $status,
            ]);
        }

        // Fallback si alguna vez lo llamas vía formulario normal
        return back();
    }

    protected function ensureCanViewDeals(Pipeline $pipeline)
{
    $user = Auth::user();

    if (! $pipeline->userCan($user, 'view')) {
        abort(403);
    }
}

protected function ensureCanEditDeals(Pipeline $pipeline)
{
    $user = Auth::user();

    if (! $pipeline->userCan($user, 'edit')) {
        abort(403);
    }
}

}

<?php

namespace App\Http\Controllers;

use App\Models\AiFunction;
use App\Models\CustomField;
use App\Models\Pipeline;
use App\Models\WhatsappAccount;
use App\Models\WhatsappAiAssistant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiFunctionController extends Controller
{
    private function teamId(): int
    {
        return Auth::user()->currentTeam->id;
    }

    private function ensureAssistant(WhatsappAccount $account): WhatsappAiAssistant
    {
        abort_unless($account->team_id === $this->teamId(), 404);
        $assistant = WhatsappAiAssistant::where('whatsapp_account_id', $account->id)->firstOrFail();
        return $assistant;
    }

    /** Lista funciones IA de la cuenta (para el panel de configuración) */
    public function index(WhatsappAccount $account)
    {
        $assistant = $this->ensureAssistant($account);

        $functions = AiFunction::where('whatsapp_ai_assistant_id', $assistant->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json(['ok' => true, 'functions' => $functions]);
    }

    /** Devuelve la lista de campos CRM disponibles (contact + deal + custom) para usar en una función */
    public function availableFields()
    {
        $teamId = $this->teamId();

        $contactFields = [
            ['key' => 'contact.name',         'label' => '👤 Nombre completo'],
            ['key' => 'contact.first_name',   'label' => '👤 Nombre'],
            ['key' => 'contact.last_name',    'label' => '👤 Apellido'],
            ['key' => 'contact.email',        'label' => '✉️ Email'],
            ['key' => 'contact.phone',        'label' => '📞 Teléfono'],
            ['key' => 'contact.company',      'label' => '🏢 Empresa'],
            ['key' => 'contact.position',     'label' => '💼 Cargo'],
            ['key' => 'contact.tipo_doc',     'label' => '📄 Tipo doc (DNI/RUC/CE)'],
            ['key' => 'contact.num_doc',      'label' => '🔢 Número doc'],
            ['key' => 'contact.razon_social', 'label' => '🏛 Razón social'],
            ['key' => 'contact.notes',        'label' => '📝 Notas (contacto)'],
        ];

        $dealFields = [
            ['key' => 'deal.title',       'label' => '📌 Título negociación'],
            ['key' => 'deal.amount',      'label' => '💰 Monto'],
            ['key' => 'deal.currency',    'label' => '💱 Moneda'],
            ['key' => 'deal.close_date',  'label' => '📅 Fecha cierre'],
            ['key' => 'deal.description', 'label' => '📝 Descripción negociación'],
        ];

        $customContact = CustomField::where('team_id', $teamId)
            ->where('entity_type', 'contact')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($cf) => ['key' => "custom.contact.{$cf->id}", 'label' => '🧩 [Contacto] ' . $cf->name])
            ->all();

        $customDeal = CustomField::where('team_id', $teamId)
            ->where('entity_type', 'deal')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($cf) => ['key' => "custom.deal.{$cf->id}", 'label' => '🧩 [Negociación] ' . $cf->name])
            ->all();

        return response()->json([
            'ok'     => true,
            'groups' => [
                ['label' => 'Datos de contacto',          'options' => $contactFields],
                ['label' => 'Datos de negociación',       'options' => $dealFields],
                ['label' => 'Campos personalizados',      'options' => array_merge($customContact, $customDeal)],
            ],
        ]);
    }

    /** Devuelve las fases de los pipelines del team (para mode=change_stage) */
    public function availableStages()
    {
        $pipelines = Pipeline::where('team_id', $this->teamId())
            ->where('is_active', true)
            ->with(['stages' => fn($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        $groups = $pipelines->map(fn($p) => [
            'label'   => $p->name,
            'options' => $p->stages->map(fn($s) => [
                'id'    => $s->id,
                'label' => $s->name,
                'color' => $s->color ?? '#6366f1',
            ])->all(),
        ]);

        return response()->json(['ok' => true, 'groups' => $groups]);
    }

    public function store(Request $request, WhatsappAccount $account)
    {
        $assistant = $this->ensureAssistant($account);

        $data = $request->validate([
            'mode'              => ['required', 'string', \Illuminate\Validation\Rule::in([
                AiFunction::MODE_UPDATE_CRM, AiFunction::MODE_CHANGE_STAGE, AiFunction::MODE_INFO,
            ])],
            'name'              => ['required', 'string', 'regex:/^[a-z][a-z0-9_]{1,58}[a-z0-9]$/i', 'max:60'],
            'description'       => 'required|string|max:2000',
            'properties'        => 'nullable|array',
            'properties.*'      => 'string|max:120',
            'target_stage_id'   => 'nullable|integer|exists:pipeline_stages,id',
            'response_template' => 'nullable|string|max:2000',
            'is_active'         => 'nullable|boolean',
            'sort_order'        => 'nullable|integer',
        ]);

        AiFunction::create([
            'team_id'                  => $this->teamId(),
            'whatsapp_ai_assistant_id' => $assistant->id,
            'mode'                     => $data['mode'],
            'name'                     => $data['name'],
            'description'              => $data['description'],
            'properties'               => $data['properties'] ?? [],
            'target_stage_id'          => $data['target_stage_id'] ?? null,
            'response_template'        => $data['response_template'] ?? null,
            'is_active'                => $request->boolean('is_active', true),
            'sort_order'               => $data['sort_order'] ?? 0,
        ]);

        return response()->json(['ok' => true]);
    }

    public function update(Request $request, AiFunction $aiFunction)
    {
        abort_unless($aiFunction->team_id === $this->teamId(), 404);

        $data = $request->validate([
            'mode'              => ['required', 'string', \Illuminate\Validation\Rule::in([
                AiFunction::MODE_UPDATE_CRM, AiFunction::MODE_CHANGE_STAGE, AiFunction::MODE_INFO,
            ])],
            'name'              => ['required', 'string', 'regex:/^[a-z][a-z0-9_]{1,58}[a-z0-9]$/i', 'max:60'],
            'description'       => 'required|string|max:2000',
            'properties'        => 'nullable|array',
            'properties.*'      => 'string|max:120',
            'target_stage_id'   => 'nullable|integer|exists:pipeline_stages,id',
            'response_template' => 'nullable|string|max:2000',
            'is_active'         => 'nullable|boolean',
            'sort_order'        => 'nullable|integer',
        ]);

        $aiFunction->update([
            'mode'              => $data['mode'],
            'name'              => $data['name'],
            'description'       => $data['description'],
            'properties'        => $data['properties'] ?? [],
            'target_stage_id'   => $data['target_stage_id'] ?? null,
            'response_template' => $data['response_template'] ?? null,
            'is_active'         => $request->boolean('is_active', $aiFunction->is_active),
            'sort_order'        => $data['sort_order'] ?? $aiFunction->sort_order,
        ]);

        return response()->json(['ok' => true]);
    }

    public function destroy(AiFunction $aiFunction)
    {
        abort_unless($aiFunction->team_id === $this->teamId(), 404);
        $aiFunction->delete();
        return response()->json(['ok' => true]);
    }
}

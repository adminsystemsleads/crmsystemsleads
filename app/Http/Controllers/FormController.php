<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use App\Models\Form;
use App\Models\FormField;
use App\Models\Pipeline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FormController extends Controller
{
    protected function currentTeam()
    {
        return Auth::user()->currentTeam;
    }

    /** Campos base del contacto que se pueden agregar al formulario. */
    private const CORE_KEYS = ['name', 'email', 'phone', 'company'];

    public function index()
    {
        $team = $this->currentTeam();

        $forms = Form::where('team_id', $team->id)
            ->withCount('submissions')
            ->orderByDesc('id')
            ->get();

        return view('forms.index', compact('forms'));
    }

    public function create()
    {
        return view('forms.builder', array_merge(
            ['form' => null, 'fields' => []],
            $this->builderData()
        ));
    }

    public function store(Request $request)
    {
        $team = $this->currentTeam();
        $data = $this->validated($request, $team);

        $data['team_id'] = $team->id;
        $data['slug']    = Form::generateUniqueSlug();

        $form = Form::create($data);
        $this->syncFields($form, $request->input('fields_json'), $team->id);

        return redirect()->route('formularios.edit', $form)
            ->with('success', __('Formulario creado. Comparte el enlace o incrústalo en tu web.'));
    }

    public function edit(Form $form)
    {
        abort_unless($form->team_id === $this->currentTeam()->id, 404);

        $fields = $form->fields()->with('customField')->get()->map(fn ($f) => [
            'source'          => $f->source,
            'core_key'        => $f->core_key,
            'custom_field_id' => $f->custom_field_id,
            'label'           => $f->label,
            'placeholder'     => $f->placeholder,
            'is_required'     => (bool) $f->is_required,
        ])->values()->all();

        return view('forms.builder', array_merge(
            ['form' => $form, 'fields' => $fields],
            $this->builderData()
        ));
    }

    public function update(Request $request, Form $form)
    {
        abort_unless($form->team_id === $this->currentTeam()->id, 404);

        $team = $this->currentTeam();
        $data = $this->validated($request, $team);

        $form->update($data);
        $this->syncFields($form, $request->input('fields_json'), $team->id);

        return redirect()->route('formularios.edit', $form)
            ->with('success', __('Formulario actualizado.'));
    }

    public function destroy(Form $form)
    {
        abort_unless($form->team_id === $this->currentTeam()->id, 404);

        $form->delete();

        return redirect()->route('formularios.index')
            ->with('success', __('Formulario eliminado.'));
    }

    public function submissions(Form $form)
    {
        abort_unless($form->team_id === $this->currentTeam()->id, 404);

        $submissions = $form->submissions()
            ->with(['contact', 'deal'])
            ->paginate(30);

        return view('forms.submissions', compact('form', 'submissions'));
    }

    /* ================= Helpers ================= */

    /** Datos comunes que necesita el constructor (embudos, usuarios, campos personalizados). */
    private function builderData(): array
    {
        $team = $this->currentTeam();

        $pipelines = Pipeline::where('team_id', $team->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->with(['stages' => fn ($q) => $q->orderBy('sort_order')])
            ->get()
            ->map(fn ($p) => [
                'id'     => $p->id,
                'name'   => $p->name,
                'stages' => $p->stages->map(fn ($s) => [
                    'id'      => $s->id,
                    'name'    => $s->name,
                    'is_won'  => (bool) $s->is_won,
                    'is_lost' => (bool) $s->is_lost,
                ])->values()->all(),
            ])->values()->all();

        $users = $team->allUsers()
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])
            ->values()->all();

        $customFields = CustomField::where('team_id', $team->id)
            ->whereIn('entity_type', ['contact', 'deal'])
            ->where('is_active', true)
            ->orderBy('sort_order')->orderBy('id')
            ->get()
            ->map(fn ($c) => [
                'id'          => $c->id,
                'name'        => $c->name,
                'entity_type' => $c->entity_type,
                'field_type'  => $c->field_type,
            ])->values()->all();

        return compact('pipelines', 'users', 'customFields');
    }

    /** Valida y arma los datos del formulario (sin los campos). */
    private function validated(Request $request, $team): array
    {
        $teamUserIds = $team->allUsers()->pluck('id')->all();
        $hex = 'regex:/^#[0-9A-Fa-f]{6,8}$/';

        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'title'           => 'nullable|string|max:255',
            'subtitle'        => 'nullable|string|max:500',
            'button_text'     => 'nullable|string|max:60',
            'success_message' => 'nullable|string|max:1000',
            'redirect_url'    => 'nullable|url|max:500',

            'bg_color'          => ['nullable', $hex],
            'card_color'        => ['nullable', $hex],
            'text_color'        => ['nullable', $hex],
            'primary_color'     => ['nullable', $hex],
            'button_text_color' => ['nullable', $hex],

            'pipeline_id'      => ['nullable', Rule::exists('pipelines', 'id')->where('team_id', $team->id)],
            'stage_id'         => ['nullable', 'integer'],
            'move_stage_id'    => ['nullable', 'integer'],
            'assigned_user_id' => ['nullable', Rule::in($teamUserIds)],
            'deal_title_template' => 'nullable|string|max:255',
            'deal_dedup_mode'  => ['required', Rule::in(['always_create', 'use_active'])],
            'is_active'        => 'nullable|boolean',
        ]);

        // Normaliza booleans / defaults
        $data['button_text'] = $data['button_text'] ?: 'Enviar';
        $data['is_active']   = $request->boolean('is_active');
        if (empty($data['deal_title_template'])) {
            $data['deal_title_template'] = '{form} - {name}';
        }

        // Verifica que las etapas pertenezcan al pipeline elegido (si hay pipeline).
        $data['stage_id']      = $this->validStageForPipeline($data['stage_id'] ?? null, $data['pipeline_id'] ?? null);
        $data['move_stage_id'] = $data['deal_dedup_mode'] === 'use_active'
            ? $this->validStageForPipeline($data['move_stage_id'] ?? null, $data['pipeline_id'] ?? null)
            : null;

        return $data;
    }

    /** Devuelve el stage id sólo si pertenece al pipeline; si no, null. */
    private function validStageForPipeline($stageId, $pipelineId)
    {
        if (!$stageId || !$pipelineId) return null;

        $ok = \App\Models\PipelineStage::where('id', $stageId)
            ->where('pipeline_id', $pipelineId)
            ->exists();

        return $ok ? (int) $stageId : null;
    }

    /** Reemplaza los campos del formulario a partir del JSON del constructor. */
    private function syncFields(Form $form, $json, int $teamId): void
    {
        $items = json_decode((string) $json, true);
        if (!is_array($items)) $items = [];

        // IDs de campos personalizados válidos para el team
        $validCustomIds = CustomField::where('team_id', $teamId)
            ->whereIn('entity_type', ['contact', 'deal'])
            ->where('is_active', true)
            ->pluck('id')->all();

        $form->fields()->delete();

        $sort = 0;
        $hasName = false;

        foreach ($items as $item) {
            $source = ($item['source'] ?? 'core') === 'custom' ? 'custom' : 'core';

            if ($source === 'custom') {
                $cid = (int) ($item['custom_field_id'] ?? 0);
                if (!in_array($cid, $validCustomIds, true)) continue;
                $coreKey = null;
            } else {
                $coreKey = $item['core_key'] ?? null;
                if (!in_array($coreKey, self::CORE_KEYS, true)) continue;
                if ($coreKey === 'name') $hasName = true;
                $cid = null;
            }

            FormField::create([
                'form_id'         => $form->id,
                'source'          => $source,
                'core_key'        => $coreKey,
                'custom_field_id' => $cid,
                'label'           => $item['label'] ?? null,
                'placeholder'     => $item['placeholder'] ?? null,
                'is_required'     => (bool) ($item['is_required'] ?? false),
                'sort_order'      => $sort++,
            ]);
        }

        // Garantiza siempre el campo Nombre (obligatorio) al inicio.
        if (!$hasName) {
            FormField::create([
                'form_id'     => $form->id,
                'source'      => 'core',
                'core_key'    => 'name',
                'is_required' => true,
                'sort_order'  => -1,
            ]);
        }
    }
}

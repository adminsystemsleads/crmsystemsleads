<?php

namespace App\Http\Controllers;

use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Deal;
use App\Models\Contact;
use App\Models\WhatsappAccount;
use App\Services\WhatsappTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;


class PipelineController extends Controller
{
    protected function currentTeam()
    {
        return Auth::user()->currentTeam; // Jetstream Teams
    }

    protected function pipelineForTeam($id)
    {
        $team = $this->currentTeam();

        return Pipeline::where('team_id', $team->id)
            ->where('id', $id)
            ->firstOrFail();
    }

    // LISTADO DE PIPELINES
    public function index()
    {
        $team = $this->currentTeam();

        $pipelines = Pipeline::where('team_id', $team->id)
            ->with('stages')
            ->orderBy('sort_order')
            ->get();

        return view('pipelines.index', compact('pipelines'));
    }

    // CREAR PIPELINE
    public function create()
    {
        return view('pipelines.create');
    }

    public function store(Request $request)
    {
        $team = $this->currentTeam();

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $sortOrder = Pipeline::where('team_id', $team->id)->max('sort_order') + 1;

        $pipeline = Pipeline::create([
            'team_id'     => $team->id,
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'sort_order'  => $sortOrder,
            'is_default'  => false,
            'is_active'   => true,
        ]);

        // Etapas por defecto para todo embudo nuevo
        $defaultStages = [
            ['name' => 'Nuevo',           'probability' => 10,  'color' => '#3b82f6', 'is_won' => false, 'is_lost' => false],
            ['name' => 'En desarrollo',   'probability' => 40,  'color' => '#6366f1', 'is_won' => false, 'is_lost' => false],
            ['name' => 'Seguimiento',     'probability' => 70,  'color' => '#f59e0b', 'is_won' => false, 'is_lost' => false],
            ['name' => 'Cerrado Ganada',  'probability' => 100, 'color' => '#16a34a', 'is_won' => true,  'is_lost' => false],
            ['name' => 'Cerrado Perdido', 'probability' => 0,   'color' => '#dc2626', 'is_won' => false, 'is_lost' => true],
        ];

        foreach ($defaultStages as $i => $s) {
            PipelineStage::create([
                'pipeline_id' => $pipeline->id,
                'name'        => $s['name'],
                'slug'        => \Str::slug($s['name']),
                'sort_order'  => $i + 1,
                'probability' => $s['probability'],
                'color'       => $s['color'],
                'is_won'      => $s['is_won'],
                'is_lost'     => $s['is_lost'],
            ]);
        }

        return redirect()->route('pipelines.index')
            ->with('status', 'Pipeline creado con sus etapas por defecto.');
    }

    // EDITAR PIPELINE (y sus fases)
    public function edit(Pipeline $pipeline)
    {
        $pipeline = $this->pipelineForTeam($pipeline->id);
        Gate::authorize('configure', $pipeline);

        $pipeline->load(['stages' => function ($q) {
            $q->orderBy('sort_order');
        }]);

        return view('pipelines.edit', compact('pipeline'));
    }

    public function update(Request $request, Pipeline $pipeline)
    {
        $pipeline = $this->pipelineForTeam($pipeline->id);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
            'show_in_nav' => 'nullable|boolean',
        ]);

        $pipeline->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active'   => $request->boolean('is_active'),
            'show_in_nav' => $request->boolean('show_in_nav'),
        ]);

        return redirect()->route('pipelines.index')
            ->with('status', 'Pipeline actualizado correctamente.');
    }

    public function destroy(Pipeline $pipeline)
    {
        $pipeline = $this->pipelineForTeam($pipeline->id);

        // OJO: aquí también se borran sus deals por la FK si así la configuraste.
        $pipeline->delete();

        return redirect()->route('pipelines.index')
            ->with('status', 'Pipeline eliminado.');
    }

    /* ==========================
     *  FASES (STAGES) DEL PIPELINE
     * ==========================*/

    public function storeStage(Request $request, Pipeline $pipeline)
    {
        $pipeline = $this->pipelineForTeam($pipeline->id);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255',
            'probability' => 'nullable|integer|min:0|max:100',
            'color'       => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'is_won'      => 'nullable|boolean',
            'is_lost'     => 'nullable|boolean',
        ]);

        $sortOrder = $pipeline->stages()->max('sort_order') + 1;

        PipelineStage::create([
            'pipeline_id' => $pipeline->id,
            'name'        => $data['name'],
            'slug'        => ($data['slug'] ?? null) ?: \Str::slug($data['name']),
            'sort_order'  => $sortOrder,
            'probability' => $data['probability'] ?? null,
            'color'       => $data['color'] ?? '#6366f1',
            'is_won'      => $request->boolean('is_won'),
            'is_lost'     => $request->boolean('is_lost'),
        ]);

        return back()->with('status', 'Fase creada correctamente.');
    }

    /**
     * Guardar todas las fases del pipeline en un solo request.
     */
    public function updateStagesBulk(Request $request, Pipeline $pipeline)
    {
        $pipeline = $this->pipelineForTeam($pipeline->id);

        $data = $request->validate([
            'stages'                  => 'required|array',
            'stages.*.name'           => 'required|string|max:255',
            'stages.*.slug'           => 'nullable|string|max:255',
            'stages.*.probability'    => 'nullable|integer|min:0|max:100',
            'stages.*.color'          => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'stages.*.is_won'         => 'nullable|boolean',
            'stages.*.is_lost'        => 'nullable|boolean',
            'stages.*.sort_order'     => 'nullable|integer',
        ]);

        $validIds = PipelineStage::where('pipeline_id', $pipeline->id)->pluck('id')->all();

        foreach ($data['stages'] as $stageId => $row) {
            if (!in_array((int) $stageId, $validIds, true)) continue;

            $stage = PipelineStage::find($stageId);
            if (!$stage) continue;

            $stage->update([
                'name'        => $row['name'],
                'slug'        => ($row['slug'] ?? null) ?: \Str::slug($row['name']),
                'probability' => $row['probability'] ?? null,
                'color'       => $row['color'] ?? ($stage->color ?? '#6366f1'),
                'is_won'      => filter_var($row['is_won']  ?? false, FILTER_VALIDATE_BOOLEAN),
                'is_lost'     => filter_var($row['is_lost'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'sort_order'  => $row['sort_order'] ?? $stage->sort_order,
            ]);
        }

        return back()->with('status', 'Fases actualizadas correctamente.');
    }

    public function updateStage(Request $request, Pipeline $pipeline, PipelineStage $stage)
    {
        $pipeline = $this->pipelineForTeam($pipeline->id);

        abort_unless($stage->pipeline_id === $pipeline->id, 404);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255',
            'probability' => 'nullable|integer|min:0|max:100',
            'color'       => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'is_won'      => 'nullable|boolean',
            'is_lost'     => 'nullable|boolean',
            'sort_order'  => 'nullable|integer',
        ]);

        $stage->update([
            'name'        => $data['name'],
            'slug'        => ($data['slug'] ?? null) ?: \Str::slug($data['name']),
            'probability' => $data['probability'] ?? null,
            'color'       => $data['color'] ?? ($stage->color ?? '#6366f1'),
            'is_won'      => $request->boolean('is_won'),
            'is_lost'     => $request->boolean('is_lost'),
            'sort_order'  => $data['sort_order'] ?? $stage->sort_order,
        ]);

        return back()->with('status', 'Fase actualizada.');
    }

    public function destroyStage(Pipeline $pipeline, PipelineStage $stage)
    {
        $pipeline = $this->pipelineForTeam($pipeline->id);

        abort_unless($stage->pipeline_id === $pipeline->id, 404);

        $stage->delete();

        return back()->with('status', 'Fase eliminada.');
    }

    /* ============
     *   KANBAN
     * ============*/

    public function kanban(Pipeline $pipeline, Request $request)
    {
        $pipeline = $this->pipelineForTeam($pipeline->id);

        $pipeline->load(['stages' => function ($q) {
            $q->orderBy('sort_order');
        }]);

        $stages = $pipeline->stages;

        $team   = Auth::user()->currentTeam;
        $teamId = $team->id;
        $teamTz = $team->effectiveTimezone();

        $q            = $request->query('q');
        $createdFrom  = $request->query('created_from');
        $createdTo    = $request->query('created_to');
        $months       = array_values(array_filter((array) $request->query('months', [])));
        $responsibles = array_values(array_filter((array) $request->query('responsibles', [])));
        $stageFilter  = array_values(array_filter((array) $request->query('stages', [])));
        $cf           = (array) $request->query('cf', []);

        $dealFields = \App\Support\CustomFieldsHelper::fieldsFor($teamId, 'deal');

        $dealsQuery = $this->buildDealsQuery($pipeline, $request, $teamTz, $dealFields)->with('contact');

        $dealsByStage = $dealsQuery->get()->groupBy('stage_id');
        $total        = $dealsByStage->flatten()->count();

        $viewMode = $request->query('view', 'kanban'); // kanban | table

        // Cuentas de WhatsApp activas (para el envío masivo de plantillas).
        $waAccounts = WhatsappAccount::where('team_id', $teamId)
            ->where('is_active', true)
            ->orderBy('name')->get(['id', 'name']);

        // Opciones para los filtros.
        $teamMembers = $team->allUsers()->map(fn($u) => ['id' => $u->id, 'name' => $u->name])->values();
        $monthsList  = Deal::where('pipeline_id', $pipeline->id)
            ->orderByDesc('created_at')->pluck('created_at')
            ->map(fn($d) => $d?->copy()->setTimezone($teamTz)->format('Y-m'))
            ->filter()->unique()->values();

        $filters = [
            'createdFrom'  => $createdFrom,
            'createdTo'    => $createdTo,
            'months'       => $months,
            'responsibles' => $responsibles,
            'stages'       => $stageFilter,
            'cf'           => $cf,
        ];

        return view('pipelines.kanban', compact(
            'pipeline', 'stages', 'dealsByStage', 'viewMode', 'total', 'waAccounts',
            'q', 'filters', 'teamMembers', 'monthsList', 'dealFields'
        ));
    }

    /**
     * Construye la query de negociaciones del pipeline aplicando búsqueda + filtros.
     * Reutilizada por kanban() y el envío masivo de plantillas.
     */
    protected function buildDealsQuery(Pipeline $pipeline, Request $request, $teamTz, $dealFields = null)
    {
        $teamId = Auth::user()->currentTeam->id;
        $dealFields = $dealFields ?? \App\Support\CustomFieldsHelper::fieldsFor($teamId, 'deal');

        $q            = $request->query('q');
        $createdFrom  = $request->query('created_from');
        $createdTo    = $request->query('created_to');
        $months       = array_values(array_filter((array) $request->query('months', [])));
        $responsibles = array_values(array_filter((array) $request->query('responsibles', [])));
        $stageFilter  = array_values(array_filter((array) $request->query('stages', [])));
        $cf           = (array) $request->query('cf', []);

        $dealsQuery = Deal::where('pipeline_id', $pipeline->id)
            ->when($q, fn($query) => $query->where(function ($s) use ($q) {
                $s->where('title', 'like', "%{$q}%")
                  ->orWhereHas('contact', fn($c) => $c->where('name', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%"));
            }))
            ->when($createdFrom, fn($query) => $query->where('created_at', '>=', Carbon::parse($createdFrom, $teamTz)->startOfDay()->utc()))
            ->when($createdTo, fn($query) => $query->where('created_at', '<=', Carbon::parse($createdTo, $teamTz)->endOfDay()->utc()))
            ->when($months, fn($query) => $query->where(function ($w) use ($months, $teamTz) {
                foreach ($months as $m) {
                    try { $start = Carbon::createFromFormat('Y-m-d', $m . '-01', $teamTz)->startOfMonth(); }
                    catch (\Throwable $e) { continue; }
                    $w->orWhereBetween('created_at', [$start->copy()->utc(), $start->copy()->endOfMonth()->utc()]);
                }
            }))
            ->when($responsibles, fn($query) => $query->whereIn('responsible_id', $responsibles))
            ->when($stageFilter, fn($query) => $query->whereIn('stage_id', $stageFilter));

        foreach ($dealFields as $field) {
            $val  = $cf[$field->id] ?? null;
            $vals = array_values(array_filter(is_array($val) ? $val : [$val], fn($v) => $v !== null && $v !== ''));
            if (empty($vals)) continue;

            $dealsQuery->whereHas('customFieldValues', function ($v) use ($field, $vals) {
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

        return $dealsQuery;
    }

    /**
     * Envío masivo de una plantilla de WhatsApp a los contactos de las negociaciones filtradas.
     * Procesa por lotes (offset/limit) y reporta progreso al frontend.
     */
    public function bulkSendTemplate(Pipeline $pipeline, Request $request, WhatsappTemplateService $service)
    {
        $pipeline = $this->pipelineForTeam($pipeline->id);
        $team     = Auth::user()->currentTeam;
        $teamId   = $team->id;
        $teamTz   = $team->effectiveTimezone();

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

        $headerMedia = (!empty($data['header_format']) && !empty($data['header_media']))
            ? ['format' => $data['header_format'], 'link' => $data['header_media']]
            : null;

        // Contactos únicos (con teléfono) de las negociaciones filtradas.
        $contactIdSub = $this->buildDealsQuery($pipeline, $request, $teamTz)
            ->whereNotNull('contact_id')->select('contact_id');

        $base = Contact::whereIn('id', $contactIdSub)
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

            $bodyParams = array_map(fn($v) => str_ireplace('{nombre}', (string) $c->name, (string) $v), $vars);

            $res = $service->sendTemplate($account, $phone, $data['template'], $data['language'], $bodyParams, [], $headerMedia);

            if ($res['ok'] ?? false) {
                $sent++;
            } else {
                $failed++;
                if (count($errors) < 3) $errors[] = $c->name . ': ' . ($res['message'] ?? 'error');
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
}

<?php

namespace App\Http\Controllers;

use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Deal;
use Illuminate\Http\Request;
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

        Pipeline::create([
            'team_id'     => $team->id,
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'sort_order'  => $sortOrder,
            'is_default'  => false,
            'is_active'   => true,
        ]);

        return redirect()->route('pipelines.index')
            ->with('status', 'Pipeline creado correctamente.');
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

    public function kanban(Pipeline $pipeline)
    {
        $pipeline = $this->pipelineForTeam($pipeline->id);

        $pipeline->load(['stages' => function ($q) {
        $q->orderBy('sort_order');
    }]);

    $stages = $pipeline->stages;

    $dealsByStage = Deal::where('pipeline_id', $pipeline->id)
        ->with('contact')
        ->get()
        ->groupBy('stage_id');

    $viewMode = request('view', 'kanban'); // 👈 kanban | table

    return view('pipelines.kanban', compact('pipeline', 'stages', 'dealsByStage', 'viewMode'));
    }
}

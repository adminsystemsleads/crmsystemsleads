<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CustomFieldController extends Controller
{
    private function teamId(): int
    {
        return Auth::user()->currentTeam->id;
    }

    private const ALLOWED_ENTITIES   = ['contact', 'deal'];
    private const ALLOWED_FIELD_TYPES = ['text', 'number', 'date', 'select'];

    public function index(Request $request)
    {
        $entity = $request->query('entity', 'contact');
        if (!in_array($entity, self::ALLOWED_ENTITIES, true)) {
            abort(404);
        }

        $fields = CustomField::where('team_id', $this->teamId())
            ->where('entity_type', $entity)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('custom-fields.index', compact('fields', 'entity'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'entity_type' => ['required', 'string', \Illuminate\Validation\Rule::in(self::ALLOWED_ENTITIES)],
            'name'        => 'required|string|max:120',
            'field_type'  => ['required', 'string', \Illuminate\Validation\Rule::in(self::ALLOWED_FIELD_TYPES)],
            'list_mode'   => 'nullable|in:single,multiple',
            'options'     => 'nullable|string|max:2000', // texto multilínea con una opción por línea
            'is_required' => 'nullable|boolean',
            'sort_order'  => 'nullable|integer',
        ]);

        $fieldType = $this->effectiveType($data['field_type'], $request->input('list_mode'));
        $slug = $this->uniqueSlug($data['name'], $data['entity_type']);

        CustomField::create([
            'team_id'     => $this->teamId(),
            'entity_type' => $data['entity_type'],
            'name'        => $data['name'],
            'slug'        => $slug,
            'field_type'  => $fieldType,
            'options'     => $this->normalizeOptions($data['options'] ?? null, $fieldType),
            'is_required' => $request->boolean('is_required'),
            'is_active'   => true,
            'sort_order'  => $data['sort_order'] ?? 0,
        ]);

        return back()->with('status', 'Campo personalizado creado.');
    }

    public function update(Request $request, CustomField $customField)
    {
        abort_unless($customField->team_id === $this->teamId(), 404);

        $data = $request->validate([
            'name'        => 'required|string|max:120',
            'field_type'  => ['required', 'string', \Illuminate\Validation\Rule::in(self::ALLOWED_FIELD_TYPES)],
            'list_mode'   => 'nullable|in:single,multiple',
            'options'     => 'nullable|string|max:2000',
            'is_required' => 'nullable|boolean',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer',
        ]);

        $fieldType = $this->effectiveType($data['field_type'], $request->input('list_mode'));

        $customField->update([
            'name'        => $data['name'],
            'field_type'  => $fieldType,
            'options'     => $this->normalizeOptions($data['options'] ?? null, $fieldType),
            'is_required' => $request->boolean('is_required'),
            'is_active'   => $request->boolean('is_active', $customField->is_active),
            'sort_order'  => $data['sort_order'] ?? $customField->sort_order,
        ]);

        return back()->with('status', 'Campo actualizado.');
    }

    public function destroy(CustomField $customField)
    {
        abort_unless($customField->team_id === $this->teamId(), 404);
        $customField->delete();
        return back()->with('status', 'Campo eliminado.');
    }

    private function uniqueSlug(string $name, string $entity): string
    {
        $base = Str::slug($name, '_') ?: 'campo';
        $slug = $base;
        $i = 2;
        while (
            CustomField::where('team_id', $this->teamId())
                ->where('entity_type', $entity)
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '_' . $i++;
        }
        return $slug;
    }

    /** 'select' + modo múltiple => 'multiselect'. */
    private function effectiveType(string $fieldType, ?string $listMode): string
    {
        if ($fieldType === 'select' && $listMode === 'multiple') {
            return 'multiselect';
        }
        return $fieldType;
    }

    private function normalizeOptions(?string $rawOptions, string $type): ?array
    {
        if (!in_array($type, ['select', 'multiselect'], true)) return null;
        if (!$rawOptions) return [];

        return collect(preg_split('/\r\n|\r|\n/', $rawOptions))
            ->map(fn($l) => trim($l))
            ->filter(fn($l) => $l !== '')
            ->values()
            ->all();
    }
}

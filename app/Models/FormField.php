<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    protected $fillable = [
        'form_id', 'source', 'core_key', 'custom_field_id',
        'label', 'placeholder', 'is_required', 'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    /** Etiquetas por defecto de los campos base. */
    public const CORE_LABELS = [
        'name'    => 'Nombre',
        'email'   => 'Correo electrónico',
        'phone'   => 'Teléfono',
        'company' => 'Empresa',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function customField()
    {
        return $this->belongsTo(CustomField::class);
    }

    /** Nombre del input HTML que usará este campo. */
    public function inputName(): string
    {
        if ($this->source === 'custom' && $this->custom_field_id) {
            return "custom_fields[{$this->custom_field_id}]";
        }
        return $this->core_key ?? '';
    }

    /** Etiqueta a mostrar (override o etiqueta por defecto). */
    public function displayLabel(): string
    {
        if ($this->label) return $this->label;
        if ($this->source === 'custom') return $this->customField?->name ?? 'Campo';
        return self::CORE_LABELS[$this->core_key] ?? ucfirst((string) $this->core_key);
    }
}

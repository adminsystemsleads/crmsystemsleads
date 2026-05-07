<?php

namespace App\Support;

use App\Models\CustomField;
use App\Models\CustomFieldValue;
use Illuminate\Database\Eloquent\Model;

class CustomFieldsHelper
{
    /**
     * Devuelve los campos personalizados activos para una entidad.
     */
    public static function fieldsFor(int $teamId, string $entityType)
    {
        return CustomField::where('team_id', $teamId)
            ->where('entity_type', $entityType)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * Devuelve los valores guardados de un modelo como array [field_id => value].
     */
    public static function valuesFor(Model $model): array
    {
        return CustomFieldValue::where('valuable_type', get_class($model))
            ->where('valuable_id', $model->getKey())
            ->pluck('value', 'custom_field_id')
            ->toArray();
    }

    /**
     * Sincroniza los valores enviados en el request (custom_fields[<id>] => valor) con el modelo.
     * Filtra por team_id y entity_type para evitar manipulación.
     */
    public static function sync(Model $model, ?array $payload, int $teamId, string $entityType): void
    {
        if (!is_array($payload)) return;

        $validIds = CustomField::where('team_id', $teamId)
            ->where('entity_type', $entityType)
            ->pluck('id')
            ->all();

        $type = get_class($model);
        $id   = $model->getKey();

        foreach ($payload as $fieldId => $value) {
            $fieldId = (int) $fieldId;
            if (!in_array($fieldId, $validIds, true)) continue;

            $value = is_array($value) ? json_encode($value) : (string) $value;
            $value = trim($value);

            if ($value === '') {
                CustomFieldValue::where('custom_field_id', $fieldId)
                    ->where('valuable_type', $type)
                    ->where('valuable_id', $id)
                    ->delete();
                continue;
            }

            CustomFieldValue::updateOrCreate(
                [
                    'custom_field_id' => $fieldId,
                    'valuable_type'   => $type,
                    'valuable_id'     => $id,
                ],
                ['value' => $value]
            );
        }
    }
}

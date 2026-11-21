<?php

namespace App\Support\CustomFields;

use App\Models\CustomField;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomFieldManager
{
    public function activeFields(string $entityType): Collection
    {
        return CustomField::query()
            ->forEntity($entityType)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function validationRules(string $entityType): array
    {
        $fields = $this->activeFields($entityType);
        $rules = ['custom_data' => ['nullable', 'array']];

        foreach ($fields as $field) {
            $key = 'custom_data.'.$field->field_key;
            $base = $field->is_required ? ['required'] : ['nullable'];

            $rules[$key] = match ($field->type) {
                'textarea' => [...$base, 'string', 'max:5000'],
                'number' => [...$base, 'numeric', 'between:-999999999,999999999'],
                'date' => [...$base, 'date'],
                'email' => [...$base, 'email', 'max:255'],
                'url' => [...$base, 'url', 'max:2048'],
                'select' => [...$base, 'string', Rule::in($field->options ?? [])],
                'checkbox' => $field->is_required
                    ? ['accepted']
                    : ['nullable', 'boolean'],
                default => [...$base, 'string', 'max:255'],
            };
        }

        return $rules;
    }

    public function validatedValues(Request $request, string $entityType, array $existing = []): array
    {
        $submitted = $request->input('custom_data', []);
        $submitted = is_array($submitted) ? $submitted : [];
        $values = $existing;

        foreach ($this->activeFields($entityType) as $field) {
            $value = $submitted[$field->field_key] ?? null;

            if ($field->type === 'checkbox') {
                $values[$field->field_key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                continue;
            }

            if (is_string($value)) {
                $value = trim($value);
            }

            $values[$field->field_key] = $value === '' ? null : $value;
        }

        return $values;
    }
}

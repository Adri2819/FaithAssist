<?php

namespace App\Http\Requests\Concerns;

trait UppercasesFields
{
    protected function prepareForValidation(): void
    {
        $fields = $this->textFields();

        if (empty($fields)) {
            return;
        }

        $this->merge(
            collect($fields)
                ->filter(fn ($field) => $this->has($field) && is_string($this->input($field)))
                ->mapWithKeys(fn ($field) => [$field => mb_strtoupper(trim($this->input($field)))])
                ->all(),
        );
    }

    protected function textFields(): array
    {
        return [];
    }
}
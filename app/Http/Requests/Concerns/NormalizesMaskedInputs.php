<?php

namespace App\Http\Requests\Concerns;

use App\Support\MaskFormatter;

trait NormalizesMaskedInputs
{
    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach ($this->maskedFields() as $field) {
            if (! $this->exists($field)) {
                continue;
            }

            $normalized[$field] = MaskFormatter::digits($this->input($field));
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }

    protected function maskedFields(): array
    {
        return [];
    }
}

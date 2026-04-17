<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'exists:branches,id'],
            'club_resource_id' => ['nullable', 'exists:club_resources,id'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:30'],
            'current_quantity' => ['nullable', 'numeric', 'min:0'],
            'minimum_quantity' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }
}

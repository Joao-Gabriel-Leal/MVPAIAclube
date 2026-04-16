<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $isAdmin = $this->user()?->isAdminMatrix() || $this->user()?->isAdminBranch();

        return [
            'club_resource_id' => ['required', 'exists:club_resources,id'],
            'member_id' => [
                Rule::requiredIf($isAdmin && ! $this->filled('dependent_id')),
                'nullable',
                'exists:members,id',
            ],
            'dependent_id' => ['nullable', 'exists:dependents,id'],
            'reservation_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'guest_count' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'member_id.required' => 'Selecione um associado responsavel ou um dependente.',
        ];
    }
}

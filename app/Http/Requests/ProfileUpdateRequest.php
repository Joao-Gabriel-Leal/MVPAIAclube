<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'profile_photo' => ['nullable', 'image', 'max:4096'],
        ];

        if ($this->user()?->isCardHolder()) {
            $rules['phone'] = ['nullable', 'string', 'max:255'];
        } else {
            $rules['name'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'profile_photo.uploaded' => 'Nao foi possivel enviar a foto da carteirinha. Tente novamente com outra imagem.',
            'profile_photo.image' => 'Envie uma imagem valida para a foto da carteirinha.',
            'profile_photo.max' => 'A foto da carteirinha deve ter no maximo 4 MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'e-mail',
            'phone' => 'telefone',
            'profile_photo' => 'foto da carteirinha',
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Support\ClubMediaSlots;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClubSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdminMatrix() ?? false;
    }

    public function rules(): array
    {
        $rules = [
            'card_prefix' => ['required', 'string', 'regex:/^[A-Z0-9]{2,6}$/'],
            'remove_slots' => ['sometimes', 'array'],
        ];

        foreach (ClubMediaSlots::keys() as $slot) {
            $definition = ClubMediaSlots::definition($slot);

            $rules[$slot] = [
                'nullable',
                'file',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:8192',
                function (string $attribute, mixed $value, \Closure $fail) use ($definition): void {
                    if (! $value) {
                        return;
                    }

                    $imageSize = @getimagesize($value->getRealPath());

                    if ($imageSize === false) {
                        $fail('Envie uma imagem valida para este espaco.');

                        return;
                    }

                    [$width, $height] = $imageSize;
                    $expectedRatio = $definition['ratio_width'] / $definition['ratio_height'];
                    $actualRatio = $height > 0 ? $width / $height : 0;

                    if ($width < $definition['min_width'] || $height < $definition['min_height']) {
                        $fail("Use uma imagem com pelo menos {$definition['min_width']} x {$definition['min_height']} px.");
                    }

                    if (abs($actualRatio - $expectedRatio) > 0.03) {
                        $fail('A proporcao desta imagem nao bate com o espaco escolhido.');
                    }
                },
            ];
        }

        $rules['remove_slots.*'] = ['required', Rule::in(ClubMediaSlots::keys())];

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'card_prefix' => strtoupper(trim((string) $this->input('card_prefix'))),
        ]);
    }
}

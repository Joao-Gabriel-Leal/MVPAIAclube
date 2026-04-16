<?php

namespace App\Support;

class MaskFormatter
{
    public static function digits(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);

        return $digits !== '' ? $digits : null;
    }

    public static function cpf(?string $value): ?string
    {
        return self::applyPattern(self::digits($value), '###.###.###-##');
    }

    public static function cnpj(?string $value): ?string
    {
        return self::applyPattern(self::digits($value), '##.###.###/####-##');
    }

    public static function cpfOrCnpj(?string $value): ?string
    {
        $digits = self::digits($value);

        if ($digits === null) {
            return null;
        }

        return strlen($digits) > 11
            ? self::cnpj($digits)
            : self::cpf($digits);
    }

    public static function phone(?string $value): ?string
    {
        $digits = self::digits($value);

        if ($digits === null) {
            return null;
        }

        if (strlen($digits) <= 2) {
            return '(' . $digits;
        }

        $formatted = '(' . substr($digits, 0, 2) . ') ';
        $subscriberNumber = substr($digits, 2);
        $prefixLength = strlen($digits) > 10 ? 5 : 4;

        if (strlen($subscriberNumber) <= $prefixLength) {
            return $formatted . $subscriberNumber;
        }

        return $formatted
            . substr($subscriberNumber, 0, $prefixLength)
            . '-'
            . substr($subscriberNumber, $prefixLength, 4);
    }

    protected static function applyPattern(?string $digits, string $pattern): ?string
    {
        if ($digits === null) {
            return null;
        }

        $formatted = '';
        $digitIndex = 0;
        $digitsLength = strlen($digits);

        foreach (str_split($pattern) as $character) {
            if ($character === '#') {
                if ($digitIndex >= $digitsLength) {
                    break;
                }

                $formatted .= $digits[$digitIndex];
                $digitIndex++;

                continue;
            }

            if ($digitIndex > 0 && $digitIndex < $digitsLength) {
                $formatted .= $character;
            }
        }

        return $formatted;
    }
}

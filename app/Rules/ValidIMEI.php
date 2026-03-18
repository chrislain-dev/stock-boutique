<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidIMEI implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // IMEI should be 15 digits for mobile phones
        // Serial number can be more flexible
        if (!preg_match('/^\d{15}$/', $value)) {
            $fail('L\'IMEI doit contenir exactement 15 chiffres.');
        }

        // Luhn algorithm check (simple IMEI validation)
        if (!$this->luhnCheck($value)) {
            $fail('L\'IMEI n\'est pas valide selon l\'algorithme Luhn.');
        }
    }

    /**
     * Validate using Luhn algorithm.
     */
    private function luhnCheck(string $imei): bool
    {
        $sum = 0;
        $alt = false;

        for ($i = strlen($imei) - 1; $i >= 0; $i--) {
            $digit = (int) $imei[$i];

            if ($alt) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
            $alt = !$alt;
        }

        return $sum % 10 === 0;
    }
}

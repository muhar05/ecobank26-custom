<?php

namespace App\Exports\Concerns;

trait SanitizesFormulaInjection
{
    /**
     * Netralkan string jika diawali karakter formula Excel (=, +, -, @, \t, \r)
     * untuk mencegah CSV/Excel Formula Injection.
     */
    protected function sanitizeCell(mixed $value): mixed
    {
        if (!is_string($value) || $value === '') {
            return $value;
        }

        // Abaikan jika sudah memiliki prefix tanda kutip tunggal
        if ($value[0] === "'") {
            return $value;
        }

        if (in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $value;
        }

        return $value;
    }
}

<?php

namespace App\Exceptions;

use Exception;

class MinimumDepositException extends Exception
{
    public function __construct(int $currentCount, int $required)
    {
        parent::__construct(
            "Nasabah belum memenuhi syarat penarikan. Minimal {$required} kali setoran sebelum bisa menarik saldo. Saat ini: {$currentCount} setoran."
        );
    }
}

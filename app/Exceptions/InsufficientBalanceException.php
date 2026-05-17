<?php

namespace App\Exceptions;

use Exception;

class InsufficientBalanceException extends Exception
{
    public float $balance;

    public function __construct(float $balance)
    {
        $this->balance = $balance;
        parent::__construct("Saldo tidak cukup. Saldo tersedia: Rp " . number_format($balance, 0, ',', '.'));
    }
}

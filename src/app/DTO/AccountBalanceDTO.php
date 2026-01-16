<?php

namespace App\DTO;

class AccountBalanceDTO
{
    public function __construct(
        public float $lastBalance,
        public string $nature
    ) {}
}
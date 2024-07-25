<?php

declare(strict_types=1);

namespace App\Service\CommissionCalculator;

use App\DTO\TransactionDTO;

interface CommissionCalculatorInterface {
    public function calculateTransactionCommission(TransactionDTO $transactionDTO): float;
}
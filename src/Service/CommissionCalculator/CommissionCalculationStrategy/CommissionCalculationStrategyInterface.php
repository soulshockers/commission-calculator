<?php

declare(strict_types=1);

namespace App\Service\CommissionCalculator\CommissionCalculationStrategy;

use App\DTO\TransactionDTO;

interface CommissionCalculationStrategyInterface
{
    public function canCalculate(TransactionDTO $transactionDTO): bool;

    public function calculate(TransactionDTO $transactionDTO): float;
}
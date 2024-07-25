<?php

declare(strict_types=1);

namespace App\Service\CommissionCalculator\CommissionCalculationStrategy;

use App\DTO\TransactionDTO;

readonly abstract class AbstractCommissionCalculationStrategy implements CommissionCalculationStrategyInterface
{
    abstract public function canCalculate(TransactionDTO $transactionDTO): bool;
    abstract public function calculate(TransactionDTO $transactionDTO): float;

    protected function calculateFixedAmount(TransactionDTO $transactionDTO): float
    {
        if ($transactionDTO->currency === 'EUR' || $transactionDTO->rate === 0.0) {
            return (float) $transactionDTO->amount;
        } else {
            return (float) ($transactionDTO->amount / $transactionDTO->rate);
        }
    }
}
<?php

declare(strict_types=1);

namespace App\Service\CommissionCalculator\CommissionCalculationStrategy;

use App\DTO\TransactionDTO;
use App\Service\CountryUtils\CountryUtilsInterface;

readonly class NonEuCommissionCalculationStrategy extends AbstractCommissionCalculationStrategy
{
    public function __construct(
        private CountryUtilsInterface $countryUtils,
    ) {}

    public function canCalculate(TransactionDTO $transactionDTO): bool
    {
        return !$this->countryUtils->isEu($transactionDTO->country);
    }

    public function calculate(TransactionDTO $transactionDTO): float
    {
        return round($this->calculateFixedAmount($transactionDTO) * 0.02, 2);
    }
}
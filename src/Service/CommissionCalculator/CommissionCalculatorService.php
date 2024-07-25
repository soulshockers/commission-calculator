<?php

declare(strict_types=1);

namespace App\Service\CommissionCalculator;

use App\DTO\TransactionDTO;
use App\Exception\NoSuitableCommissionCalculationStrategyException;
use App\Service\BinLookup\BinLookupInterface;
use App\Service\CommissionCalculator\CommissionCalculationStrategy\CommissionCalculationStrategyInterface;
use App\Service\ExchangeRate\ExchangeRateFetcherInterface;

readonly class CommissionCalculatorService implements CommissionCalculatorInterface
{
    /** @var iterable<CommissionCalculationStrategyInterface> */
    private iterable $commissionCalculationStrategies;

    public function __construct(
        private BinLookupInterface $binLookup,
        private ExchangeRateFetcherInterface $exchangeRateFetcher,
    ) {}

    /**
     * @param iterable<CommissionCalculatorInterface> $commissionCalculationStrategies
     * @return void
     */
    public function setCommissionCalculationStrategies(iterable $commissionCalculationStrategies): void
    {
        $this->commissionCalculationStrategies = $commissionCalculationStrategies;
    }

    public function calculateTransactionCommission(TransactionDTO $transactionDTO): float
    {
        $country = $this->binLookup->getCountryByBin($transactionDTO->bin);
        $rate = $this->exchangeRateFetcher->getExchangeRate($transactionDTO->currency);

        $transactionDTO = $transactionDTO
            ->setRate($rate)
            ->setCountry($country);

        foreach ($this->commissionCalculationStrategies as $commissionCalculationStrategy) {
            if ($commissionCalculationStrategy->canCalculate($transactionDTO)) {
                return $commissionCalculationStrategy->calculate($transactionDTO);
            }
        }

        throw new NoSuitableCommissionCalculationStrategyException();
    }
}
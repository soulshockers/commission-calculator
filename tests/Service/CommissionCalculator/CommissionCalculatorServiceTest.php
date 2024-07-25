<?php

declare(strict_types=1);

namespace App\Tests\Service\CommissionCalculator;

use App\DTO\CountryDTO;
use App\DTO\TransactionDTO;
use App\Exception\NoSuitableCommissionCalculationStrategyException;
use App\Service\BinLookup\BinLookupInterface;
use App\Service\CommissionCalculator\CommissionCalculationStrategy\CommissionCalculationStrategyInterface;
use App\Service\CommissionCalculator\CommissionCalculatorService;
use App\Service\ExchangeRate\ExchangeRateFetcherInterface;
use PHPUnit\Framework\TestCase;

class CommissionCalculatorServiceTest extends TestCase
{
    private BinLookupInterface $binLookup;
    private ExchangeRateFetcherInterface $exchangeRateFetcher;
    private CommissionCalculatorService $commissionCalculatorService;
    private CommissionCalculationStrategyInterface $commissionCalculationStrategy1;
    private CommissionCalculationStrategyInterface $commissionCalculationStrategy2;

    protected function setUp(): void
    {
        $this->binLookup = $this->createMock(BinLookupInterface::class);
        $this->exchangeRateFetcher = $this->createMock(ExchangeRateFetcherInterface::class);
        $this->commissionCalculationStrategy1 = $this->createMock(CommissionCalculationStrategyInterface::class);
        $this->commissionCalculationStrategy2 = $this->createMock(CommissionCalculationStrategyInterface::class);

        $this->commissionCalculatorService = new CommissionCalculatorService($this->binLookup, $this->exchangeRateFetcher);

        $this->commissionCalculatorService->setCommissionCalculationStrategies([
            $this->commissionCalculationStrategy1,
            $this->commissionCalculationStrategy2,
        ]);
    }

    public function test_calculate_transaction_commission_eu_currency(): void
    {
        // Arrange
        $transactionDTO = new TransactionDTO('45717360', 100.00, 'EUR');
        $countryDTO = new CountryDTO('DE');
        $this->binLookup->method('getCountryByBin')->willReturn($countryDTO);
        $this->exchangeRateFetcher->method('getExchangeRate')->willReturn(1.0);
        $this->commissionCalculationStrategy1
            ->method('canCalculate')
            ->with($transactionDTO)
            ->willReturn(false);
        $this->commissionCalculationStrategy2->expects($this->once())
            ->method('canCalculate')
            ->with($transactionDTO)
            ->willReturn(true);
        $this->commissionCalculationStrategy2->expects($this->once())
            ->method('calculate')
            ->with($transactionDTO)
            ->willReturn(1.0);

        // Act
        $commission = $this->commissionCalculatorService->calculateTransactionCommission($transactionDTO);

        // Assert
        $this->assertEquals(1.0, $commission);
    }

    public function test_calculate_transaction_commission_non_eu_currency_with_exchange_rate(): void
    {
        // Arrange
        $transactionDTO = new TransactionDTO('45717360', 100.00, 'USD');
        $countryDTO = new CountryDTO('DE');;
        $this->binLookup->method('getCountryByBin')->willReturn($countryDTO);
        $this->exchangeRateFetcher->method('getExchangeRate')->willReturn(1.2);
        $this->commissionCalculationStrategy1
            ->method('canCalculate')
            ->with($transactionDTO)
            ->willReturn(false);
        $this->commissionCalculationStrategy2->expects($this->once())
            ->method('canCalculate')
            ->with($transactionDTO)
            ->willReturn(true);
        $this->commissionCalculationStrategy2->expects($this->once())
            ->method('calculate')
            ->with($transactionDTO)
            ->willReturn(0.83);

        // Act
        $commission = $this->commissionCalculatorService->calculateTransactionCommission($transactionDTO);

        // Assert
        $this->assertEquals(0.83, $commission);
    }

    public function test_calculate_transaction_commission_non_eu_currency_with_zero_exchange_rate(): void
    {
        // Arrange
        $transactionDTO = new TransactionDTO('45717360', 100.00, 'USD');
        $countryDTO = new CountryDTO('DE');;
        $this->binLookup->method('getCountryByBin')->willReturn($countryDTO);
        $this->exchangeRateFetcher->method('getExchangeRate')->willReturn(0.0);
        $this->commissionCalculationStrategy1
            ->method('canCalculate')
            ->with($transactionDTO)
            ->willReturn(false);
        $this->commissionCalculationStrategy2->expects($this->once())
            ->method('canCalculate')
            ->with($transactionDTO)
            ->willReturn(true);
        $this->commissionCalculationStrategy2->expects($this->once())
            ->method('calculate')
            ->with($transactionDTO)
            ->willReturn(1.0);

        // Act
        $commission = $this->commissionCalculatorService->calculateTransactionCommission($transactionDTO);

        // Assert
        $this->assertEquals(1.0, $commission);
    }

    public function test_calculate_transaction_commission_non_eu_country(): void
    {
        // Arrange
        $transactionDTO = new TransactionDTO('45717360', 100.00, 'EUR');
        $countryDTO = new CountryDTO('DE');;
        $this->binLookup->method('getCountryByBin')->willReturn($countryDTO);
        $this->exchangeRateFetcher->method('getExchangeRate')->willReturn(1.0);
        $this->commissionCalculationStrategy1
            ->method('canCalculate')
            ->with($transactionDTO)
            ->willReturn(false);
        $this->commissionCalculationStrategy2->expects($this->once())
            ->method('canCalculate')
            ->with($transactionDTO)
            ->willReturn(true);
        $this->commissionCalculationStrategy2->expects($this->once())
            ->method('calculate')
            ->with($transactionDTO)
            ->willReturn(2.0);

        // Act
        $commission = $this->commissionCalculatorService->calculateTransactionCommission($transactionDTO);

        // Assert
        $this->assertEquals(2.0, $commission);
    }
    public function test_calculate_transaction_commission_no_suitable_strategy(): void
    {
        // Arrange
        $transactionDTO = new TransactionDTO('45717360', 100.00, 'EUR');
        $countryDTO = new CountryDTO('DE');;
        $this->binLookup->method('getCountryByBin')->willReturn($countryDTO);
        $this->exchangeRateFetcher->method('getExchangeRate')->willReturn(1.0);

        $this->commissionCalculationStrategy1
            ->method('canCalculate')
            ->with($transactionDTO)
            ->willReturn(false);
        $this->commissionCalculationStrategy2->expects($this->once())
            ->method('canCalculate')
            ->with($transactionDTO)
            ->willReturn(false);

        // Act & Assert
        $this->expectException(NoSuitableCommissionCalculationStrategyException::class);
        $this->commissionCalculatorService->calculateTransactionCommission($transactionDTO);
    }
}

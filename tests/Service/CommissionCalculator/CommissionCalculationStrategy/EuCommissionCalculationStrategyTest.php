<?php

declare(strict_types=1);

namespace App\Tests\Service\CommissionCalculator\CommissionCalculationStrategy;

use App\DTO\CountryDTO;
use App\DTO\TransactionDTO;
use App\Service\CommissionCalculator\CommissionCalculationStrategy\EuCommissionCalculationStrategy;
use App\Service\CountryUtils\CountryUtilsInterface;
use PHPUnit\Framework\TestCase;

class EuCommissionCalculationStrategyTest extends TestCase
{
    private CountryUtilsInterface $countryUtils;
    private EuCommissionCalculationStrategy $euCommissionCalculationStrategy;

    protected function setUp(): void
    {
        $this->countryUtils = $this->createMock(CountryUtilsInterface::class);
        $this->euCommissionCalculationStrategy = new EuCommissionCalculationStrategy($this->countryUtils);
    }

    public function test_can_calculate(): void
    {
        // Arrange
        $transactionDTO = new TransactionDTO('45717360', 100.00, 'EUR');
        $countryDTO = new CountryDTO('DE'); // Germany, which is in the EU
        $transactionDTO->setCountry($countryDTO);

        $this->countryUtils->expects($this->once())
            ->method('isEu')
            ->with($transactionDTO->country)
            ->willReturn(true);

        // Act
        $result = $this->euCommissionCalculationStrategy->canCalculate($transactionDTO);

        // Assert
        $this->assertTrue($result);
    }

    public function test_cannot_calculate(): void
    {
        // Arrange
        $transactionDTO = new TransactionDTO('45717360', 100.00, 'EUR');
        $countryDTO = new CountryDTO('US'); // United States, which is not in the EU
        $transactionDTO->setCountry($countryDTO);

        $this->countryUtils->expects($this->once())
            ->method('isEu')
            ->with($transactionDTO->country)
            ->willReturn(false);

        // Act
        $result = $this->euCommissionCalculationStrategy->canCalculate($transactionDTO);

        // Assert
        $this->assertFalse($result);
    }

    public function test_calculate(): void
    {
        // Arrange
        $transactionDTO = new TransactionDTO('45717360', 100.00, 'EUR');
        $countryDTO = new CountryDTO('EU');
        $transactionDTO->setCountry($countryDTO);

        // Mocking the protected method calculateFixedAmount to return a fixed amount for the test
        $euCommissionCalculationStrategy = $this->getMockBuilder(EuCommissionCalculationStrategy::class)
            ->setConstructorArgs([$this->countryUtils])
            ->onlyMethods(['calculateFixedAmount'])
            ->getMock();

        $euCommissionCalculationStrategy->expects($this->once())
            ->method('calculateFixedAmount')
            ->with($transactionDTO)
            ->willReturn(100.00);

        // Act
        $result = $euCommissionCalculationStrategy->calculate($transactionDTO);

        // Assert
        $this->assertEquals(1.00, $result); // 100.00 * 0.01 = 1.00
    }
}
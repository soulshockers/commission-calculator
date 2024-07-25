<?php

declare(strict_types=1);

namespace App\Tests\Service\CommissionCalculator\CommissionCalculationStrategy;

use App\DTO\CountryDTO;
use App\DTO\TransactionDTO;
use App\Service\CommissionCalculator\CommissionCalculationStrategy\NonEuCommissionCalculationStrategy;
use App\Service\CountryUtils\CountryUtilsInterface;
use PHPUnit\Framework\TestCase;

class NonEuCommissionCalculationStrategyTest extends TestCase
{
    private CountryUtilsInterface $countryUtils;
    private NonEuCommissionCalculationStrategy $nonEuCommissionCalculationStrategy;

    protected function setUp(): void
    {
        $this->countryUtils = $this->createMock(CountryUtilsInterface::class);
        $this->nonEuCommissionCalculationStrategy = new NonEuCommissionCalculationStrategy($this->countryUtils);
    }

    public function test_can_calculate(): void
    {
        // Arrange
        $transactionDTO = new TransactionDTO('45717360', 100.00, 'USD');
        $countryDTO = new CountryDTO('US'); // United States, which is not in the EU
        $transactionDTO->setCountry($countryDTO);

        $this->countryUtils->expects($this->once())
            ->method('isEu')
            ->with($transactionDTO->country)
            ->willReturn(false);

        // Act
        $result = $this->nonEuCommissionCalculationStrategy->canCalculate($transactionDTO);

        // Assert
        $this->assertTrue($result);
    }

    public function test_cannot_calculate(): void
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
        $result = $this->nonEuCommissionCalculationStrategy->canCalculate($transactionDTO);

        // Assert
        $this->assertFalse($result);
    }

    public function test_calculate(): void
    {
        // Arrange
        $transactionDTO = new TransactionDTO('45717360', 100.00, 'USD');
        $countryDTO = new CountryDTO('US');
        $transactionDTO->setCountry($countryDTO);

        // Mocking the protected method calculateFixedAmount to return a fixed amount for the test
        $nonEuCommissionCalculationStrategy = $this->getMockBuilder(NonEuCommissionCalculationStrategy::class)
            ->setConstructorArgs([$this->countryUtils])
            ->onlyMethods(['calculateFixedAmount'])
            ->getMock();

        $nonEuCommissionCalculationStrategy->expects($this->once())
            ->method('calculateFixedAmount')
            ->with($transactionDTO)
            ->willReturn(100.00);

        // Act
        $result = $nonEuCommissionCalculationStrategy->calculate($transactionDTO);

        // Assert
        $this->assertEquals(2.00, $result); // 100.00 * 0.02 = 2.00
    }
}

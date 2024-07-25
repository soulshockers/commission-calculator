<?php

declare(strict_types=1);

namespace App\Tests\Service\CountryUtils;

use App\DTO\CountryDTO;
use App\Service\CountryUtils\CountryUtils;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class CountryUtilsTest extends TestCase
{
    private ContainerBagInterface $params;
    private CountryUtils $countryUtils;

    protected function setUp(): void
    {
        $this->params = $this->createMock(ContainerBagInterface::class);
        $this->countryUtils = new CountryUtils($this->params);
    }

    public function test_is_eu_country(): void
    {
        // Arrange
        $countryDTO = new CountryDTO('DE'); // Germany, which is in the EU

        $this->params->expects($this->once())
            ->method('get')
            ->with('app.eu_country_codes')
            ->willReturn(['DE', 'FR', 'ES']); // Example EU country codes

        // Act
        $result = $this->countryUtils->isEu($countryDTO);

        // Assert
        $this->assertTrue($result);
    }

    public function test_is_not_eu_country(): void
    {
        // Arrange
        $countryDTO = new CountryDTO('US'); // United States, which is not in the EU

        $this->params->expects($this->once())
            ->method('get')
            ->with('app.eu_country_codes')
            ->willReturn(['DE', 'FR', 'ES']); // Example EU country codes

        // Act
        $result = $this->countryUtils->isEu($countryDTO);

        // Assert
        $this->assertFalse($result);
    }
}

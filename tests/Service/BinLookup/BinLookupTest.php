<?php

declare(strict_types=1);

namespace App\Tests\Service\BinLookup;

use App\DTO\CountryDTO;
use App\Service\BinLookup\BinLookup;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class BinLookupTest extends TestCase
{
    private HttpClientInterface $httpClient;
    private ContainerBagInterface $params;
    private BinLookup $binLookup;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->params = $this->createMock(ContainerBagInterface::class);
        $this->binLookup = new BinLookup($this->httpClient, $this->params);
    }

    public function test_get_country_alpha2_by_bin_returns_country_code(): void
    {
        // Arrange
        $bin = '45717360';
        $url = 'https://lookup.binlist.net';
        $responseData = [
            'country' => [
                'alpha2' => 'DE',
            ],
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')
            ->willReturn($responseData);

        $this->httpClient->method('request')
            ->with('GET', sprintf('%s/%s', $url, $bin))
            ->willReturn($response);

        $this->params->method('get')
            ->with('app.bin_lookup.url')
            ->willReturn($url);

        // Act
        $countryCode = $this->binLookup->getCountryByBin($bin);

        // Assert
        $this->assertEquals(new CountryDTO('DE'), $countryCode);
    }

    public function test_get_country_alpha2_by_bin_returns_null_if_country_not_found(): void
    {
        // Arrange
        $bin = '45717360';
        $url = 'https://lookup.binlist.net';
        $responseData = [
            'country' => [
                'alpha2' => null,
            ],
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')
            ->willReturn($responseData);

        $this->httpClient->method('request')
            ->with('GET', sprintf('%s/%s', $url, $bin))
            ->willReturn($response);

        $this->params->method('get')
            ->with('app.bin_lookup.url')
            ->willReturn($url);

        // Act
        $countryCode = $this->binLookup->getCountryByBin($bin);

        // Assert
        $this->assertEquals(new CountryDTO(null), $countryCode);
    }

    #[DataProvider('exception_provider')]
    public function test_get_country_alpha2_by_bin_throws_exception(string $exceptionClass): void
    {
        // Arrange
        $this->expectException($exceptionClass);

        $bin = '45717360';
        $url = 'https://lookup.binlist.net';

        $this->httpClient->method('request')
            ->with('GET', sprintf('%s/%s', $url, $bin))
            ->willThrowException($this->createMock($exceptionClass));

        $this->params->method('get')
            ->with('app.bin_lookup.url')
            ->willReturn($url);

        // Act
        $this->binLookup->getCountryByBin($bin);
    }

    public static function exception_provider(): array
    {
        return [
            [ClientExceptionInterface::class],
            [DecodingExceptionInterface::class],
            [RedirectionExceptionInterface::class],
            [ServerExceptionInterface::class],
            [TransportExceptionInterface::class],
            [ContainerExceptionInterface::class],
            [NotFoundExceptionInterface::class],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Service\ExchangeRateFetcher;

use App\Service\ExchangeRate\ExchangeRateFetcherFetcher;
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

class ExchangeRateFetcherTest extends TestCase
{
    private HttpClientInterface $httpClient;
    private ContainerBagInterface $params;
    private ExchangeRateFetcherFetcher $exchangeRateFetcher;
    public function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->params = $this->createMock(ContainerBagInterface::class);
        $this->exchangeRateFetcher = new ExchangeRateFetcherFetcher($this->httpClient, $this->params);
    }

    public function test_get_exchange_rate_returns_rate(): void
    {
        // Arrange
        $currency = 'EUR';
        $url = 'https://api.exchangeratesapi.io/v1/latest';
        $apiKey = 'test_api_key';
        $responseData = [
            'rates' => [
                'EUR' => 1,
            ],
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')
            ->willReturn($responseData);

        $this->httpClient->method('request')
            ->with('GET', $url, [
                'query' => [
                    'access_key' => $apiKey,
                ],
            ])
            ->willReturn($response);

        $this->params->method('get')
            ->willReturnMap([
                ['app.exchange_rates.url', $url],
                ['app.exchange_rates.api_key', $apiKey],
            ]);

        // Act
        $rate = $this->exchangeRateFetcher->getExchangeRate($currency);

        // Assert
        $this->assertEquals(1, $rate);
    }

    public function test_get_exchange_rate_returns_zero_if_currency_not_found(): void
    {
        // Arrange
        $currency = 'USD';
        $url = 'https://api.exchangeratesapi.io/v1/latest';
        $apiKey = 'test_api_key';
        $responseData = [
            'rates' => [
                'EUR' => 1,
            ],
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')
            ->willReturn($responseData);

        $this->httpClient->method('request')
            ->with('GET', $url, [
                'query' => [
                    'access_key' => $apiKey,
                ],
            ])
            ->willReturn($response);

        $this->params->method('get')
            ->willReturnMap([
                ['app.exchange_rates.url', $url],
                ['app.exchange_rates.api_key', $apiKey],
            ]);

        // Act
        $rate = $this->exchangeRateFetcher->getExchangeRate($currency);

        // Assert
        $this->assertEquals(0.0, $rate);
    }

    #[DataProvider('exception_provider')]
    public function test_get_exchange_rate_throws_exception(string $exceptionClass): void
    {
        // Arrange
        $this->expectException($exceptionClass);

        $currency = 'USD';
        $url = 'https://api.example.com/rates';
        $apiKey = 'test_api_key';

        $this->httpClient->method('request')
            ->with('GET', $url, [
                'query' => [
                    'access_key' => $apiKey,
                ],
            ])
            ->willThrowException($this->createMock($exceptionClass));

        $this->params->method('get')
            ->willReturnMap([
                ['app.exchange_rates.url', $url],
                ['app.exchange_rates.api_key', $apiKey],
            ]);

        // Act & Assert
        $this->exchangeRateFetcher->getExchangeRate($currency);
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
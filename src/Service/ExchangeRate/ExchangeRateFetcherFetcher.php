<?php

declare(strict_types=1);

namespace App\Service\ExchangeRate;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ExchangeRateFetcherFetcher implements ExchangeRateFetcherInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ContainerBagInterface $params,
        private array $rates = []
    ) {}

    /**
     * @param string $currency
     * @return float
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getExchangeRate(string $currency): float
    {
        // Check if the rates are already fetched
        if (!isset($this->rates[$currency])) {
            $response = $this->httpClient->request('GET', $this->params->get('app.exchange_rates.url'), [
                'query' => [
                    'access_key' => $this->params->get('app.exchange_rates.api_key'),
                ],
            ]);

            $this->rates = $response->toArray()['rates'];
        }

        return (float) ($this->rates[$currency] ?? 0.0);
    }
}
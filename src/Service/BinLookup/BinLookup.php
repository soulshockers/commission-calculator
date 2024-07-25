<?php

declare(strict_types=1);

namespace App\Service\BinLookup;

use App\DTO\CountryDTO;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class BinLookup implements BinLookupInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private ContainerBagInterface $params,
    ) {}

    /**
     * @param string $bin
     * @return CountryDTO
     * @throws ClientExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getCountryByBin(string $bin): CountryDTO
    {
        $url = sprintf("%s/%s", $this->params->get('app.bin_lookup.url'), $bin);

        $response = $this->httpClient->request('GET', $url);
        $data = $response->toArray();

        return new CountryDTO($data['country']['alpha2'] ?? null);
    }
}
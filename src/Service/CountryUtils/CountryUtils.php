<?php

declare(strict_types=1);

namespace App\Service\CountryUtils;

use App\DTO\CountryDTO;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

readonly class CountryUtils implements CountryUtilsInterface
{
    public function __construct(
        private ContainerBagInterface $params,
    ) {}

    public function isEu(CountryDTO $countryDTO): bool
    {
        return in_array($countryDTO->countryAlpha2, $this->params->get('app.eu_country_codes'));
    }
}
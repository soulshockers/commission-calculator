<?php

declare(strict_types=1);

namespace App\Service\CountryUtils;

use App\DTO\CountryDTO;

interface CountryUtilsInterface
{
    public function isEu(CountryDTO $countryDTO): bool;
}
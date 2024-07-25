<?php

declare(strict_types=1);

namespace App\Service\BinLookup;

use App\DTO\CountryDTO;

interface BinLookupInterface
{
    public function getCountryByBin(string $bin): CountryDTO;
}
<?php

declare(strict_types=1);

namespace App\DTO;

readonly class CountryDTO
{
    public function __construct(
        public ?string $countryAlpha2,
    ) {}
}
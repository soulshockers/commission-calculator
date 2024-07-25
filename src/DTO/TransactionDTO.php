<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class TransactionDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type(
            type: 'string',
            message: 'The currency field should be a string.'
        )]
        #[Assert\Regex(
            pattern: '/^\d+$/',
            message: 'The bin field should contain digits only.',
        )]
        public readonly mixed $bin,

        #[Assert\NotBlank]
        #[Assert\Type(
            type: 'string',
            message: 'The amount field should be a string.'
        )]
        #[Assert\Regex(
            pattern: '/^\d+\.\d{2}$/',
            message: 'The amount field should be a valid number with exactly two decimal places.',
        )]
        public readonly mixed $amount,

        #[Assert\NotBlank]
        #[Assert\Type(
            type: 'string',
            message: 'The currency field should be a string.'
        )]
        #[Assert\Currency(
            message: 'The currency field should be a valid ISO 4217 currency code.'
        )]
        public readonly mixed $currency,

        public float $rate = 0,
        public ?CountryDTO $country = null
    ) {}

    public function setRate(float $rate): self
    {
        $this->rate = $rate;
        return $this;
    }

    public function setCountry(CountryDTO $countryDTO): self
    {
        $this->country = $countryDTO;
        return $this;
    }
}
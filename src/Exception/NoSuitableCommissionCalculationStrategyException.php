<?php

declare(strict_types=1);

namespace App\Exception;

class NoSuitableCommissionCalculationStrategyException extends \RuntimeException
{
    public function __construct($message = 'No suitable commission calculation strategy found for the given transaction.', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
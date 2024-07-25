<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\TransactionDTO;
use App\Exception\ValidationException;
use App\Service\CommissionCalculator\CommissionCalculatorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class FileProcessor
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private CommissionCalculatorInterface $commissionCalculator,
    ) {}

    /**
     * @param string $filePath
     * @param callable $onSuccess
     * @param callable $onError
     * @throws \Exception
     */
    public function processFile(string $filePath, callable $onSuccess, callable $onError): void
    {
        $file = new \SplFileObject($filePath, 'r');

        while (!$file->eof()) {
            $lineContent = trim($file->fgets());
            $lineNumber = $file->key();

            if (empty($lineContent)) {
                continue;
            }

            try {
                /** @var TransactionDTO $transactionDTO */
                $transactionDTO = $this->serializer->deserialize($lineContent, TransactionDTO::class, 'json');
            } catch (\Throwable) {
                $onError($lineNumber, "Line $lineNumber contains invalid JSON. (Line content: $lineContent).");
                continue;
            }

            $violations = $this->validator->validate($transactionDTO);
            if (count($violations) > 0) {
                $onError($lineNumber, new ValidationException($violations));
                continue;
            }

            try {
                $onSuccess($this->commissionCalculator->calculateTransactionCommission($transactionDTO));
            } catch (\Exception $e) {
                $onError($lineNumber, "An error occurred during commission calculation: {$e->getMessage()}");
            }
        }
    }
}

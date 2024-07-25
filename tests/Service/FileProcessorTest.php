<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\DTO\TransactionDTO;
use App\Exception\ValidationException;
use App\Service\CommissionCalculator\CommissionCalculatorInterface;
use App\Service\FileProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FileProcessorTest extends TestCase
{
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;
    private CommissionCalculatorInterface $commissionCalculator;
    private FileProcessor $fileProcessor;

    protected function setUp(): void
    {
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->commissionCalculator = $this->createMock(CommissionCalculatorInterface::class);
        $this->fileProcessor = new FileProcessor($this->serializer, $this->validator, $this->commissionCalculator);
    }

    public function test_process_file_successful(): void
    {
        // Arrange
        $filePath = __DIR__ . '/../__data/input.txt';
        $transactionDTO = $this->createMock(TransactionDTO::class);
        $commission = 1.23;

        $this->serializer->method('deserialize')
            ->willReturn($transactionDTO);
        $this->validator->method('validate')
            ->willReturn(new ConstraintViolationList([])); // No violations
        $this->commissionCalculator->method('calculateTransactionCommission')
            ->willReturn($commission);

        $onSuccess = function (float $commission) use (&$successCommission) {
            $successCommission = $commission;
        };

        $onError = function (int $lineNumber, string $error) {
            $this->fail("Error occurred: $error");
        };

        // Act
        $this->fileProcessor->processFile($filePath, $onSuccess, $onError);

        // Assert
        $this->assertSame(1.23, $successCommission);
    }

    public function test_process_file_invalid_json(): void
    {
        // Arrange
        $filePath = __DIR__ . '/../__data/invalid_json_input.txt';
        $lineNumber = 5;

        $this->serializer->method('deserialize')
            ->willThrowException(new \Exception('Invalid JSON'));

        $onSuccess = function (float $commission) {
            $this->fail("Expected no success but got a commission of $commission.");
        };

        $onError = function (int $lineNumber, string $error) use (&$errorMessage) {
            $errorMessage = $error;
        };

        // Act
        $this->fileProcessor->processFile($filePath, $onSuccess, $onError);

        // Assert
        $this->assertStringContainsString("Line $lineNumber contains invalid JSON.", $errorMessage);
    }

    public function test_process_file_validation_error(): void
    {
        // Arrange
        $filePath = __DIR__ . '/../__data/invalid_format_input.txt';
        $transactionDTO = $this->createMock(TransactionDTO::class);
        $violation = $this->createMock(ConstraintViolation::class);
        $violations = new ConstraintViolationList([$violation]); // Add a single violation

        $violation->method('getMessage')
            ->willReturn('This value should not be blank.');
        $violation->method('getInvalidValue')
            ->willReturn('Invalid Value');

        $this->serializer->method('deserialize')
            ->willReturn($transactionDTO);
        $this->validator->method('validate')
            ->willReturn($violations);

        $onSuccess = function (float $commission) {
            $this->fail("Expected no success but got a commission of $commission.");
        };

        $onError = function (int $lineNumber, $error) use (&$exception) {
            $exception = $error;
        };

        // Act
        $this->fileProcessor->processFile($filePath, $onSuccess, $onError);

        // Assert
        $this->assertInstanceOf(ValidationException::class, $exception);
        $this->assertNotEmpty($exception->getViolations());
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\CalculateCommissionCommand;
use App\Service\FileProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CalculateCommissionCommandTest extends TestCase
{
    private FileProcessor $fileProcessor;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->fileProcessor = $this->createMock(FileProcessor::class);

        $command = new CalculateCommissionCommand($this->fileProcessor);
        $this->commandTester = new CommandTester($command);
    }

    public function test_execute_successful_processing(): void
    {
        // Arrange
        $filePath = 'path/to/file.txt';
        $expectedCommission = '100.00';

        $this->fileProcessor->expects($this->once())
            ->method('processFile')
            ->with(
                $filePath,
                $this->isInstanceOf(\Closure::class),
                $this->isInstanceOf(\Closure::class)
            )
            ->willReturnCallback(function ($path, $onSuccess, $onError) use ($expectedCommission) {
                // Simulate successful processing
                $onSuccess($expectedCommission);
            });

        // Act
        $this->commandTester->execute(['filePath' => $filePath]);

        // Assert
        $this->assertStringContainsString($expectedCommission, $this->commandTester->getDisplay());
        $this->assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());
    }
}

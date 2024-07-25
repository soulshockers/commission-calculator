<?php

declare(strict_types=1);

namespace App\Command;

use App\Exception\ValidationException;
use App\Service\FileProcessor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:calculate-commission')]
class CalculateCommissionCommand extends Command
{
    public function __construct(
        private readonly FileProcessor $fileProcessor,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Calculates commission from an input file.')
            ->addArgument('filePath', InputArgument::REQUIRED, 'The path to the input file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $input->getArgument('filePath');

        try {
            $this->fileProcessor->processFile(
                $filePath,
                fn ($commission) => $output->writeln("{$commission}"),
                function ($lineNumber, $error) use ($output) {
                    if ($error instanceof ValidationException) {
                        foreach ($error->getViolations() as $violation) {
                            $output->writeln("<error>Line $lineNumber encountered a validation error: \"{$violation->getInvalidValue()}\" - {$violation->getMessage()}.</error>");
                        }
                    } else {
                        $output->writeln("<error>$error</error>");
                    }
                }
            );
        } catch (\Exception $e) {
            $output->writeln("<error>An error occurred while processing the file: {$e->getMessage()}</error>");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
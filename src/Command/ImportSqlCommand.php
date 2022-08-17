<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

#[AsCommand('app:sql:import')]
final class ImportSqlCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly string $rootDir,
        private readonly Connection $connection
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('file', 'f', InputArgument::OPTIONAL, 'File path you want to import');
        $this->addOption('raw', 'r', InputArgument::OPTIONAL, 'Raw sql you want to play');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->io->title('Importing sql');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (null !== $file = $input->getOption('file')) {
            return $this->handleImportSingleFile($file);
        }

        if (null !== $raw = $input->getOption('raw')) {
            return $this->handleRawSqlImport($raw);
        }

        $this->io->info('Importing base data.');

        $finder = Finder::create();
        $files = $finder->in($this->rootDir.'/data/sql');
        foreach ($files as $file) {
            try {
                $this->handleSingleFile($file);
            } catch (\Exception $e) {
                $this->io->error($e->getMessage());
            }
        }

        return Command::SUCCESS;
    }

    private function handleSingleFile(SplFileInfo $file): void
    {
        try {
            $this->connection->executeStatement(str_replace(PHP_EOL, '', $file->getContents()));
        } catch (\Exception $e) {
            $this->io->error('Failed to import : ' . $file->getFilename() . PHP_EOL . $e->getMessage());
        }
    }

    private function handleRawSqlImport(string $raw): int
    {
        $this->io->error('Not implemented yet !');
        return Command::FAILURE;
    }

    private function handleImportSingleFile(string $file): int
    {
        $this->io->error('Not implemented yet !');
        return Command::FAILURE;
    }
}
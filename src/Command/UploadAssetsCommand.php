<?php

namespace App\Command;

use App\Infrastructure\BunnyCDN\Storage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

#[AsCommand('app:assets:upload')]
final class UploadAssetsCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly Storage $storage,
        private readonly string $rootDirectory,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->setDescription('Upload static assets to CDN');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->io->title('Uploading assets to CDN');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $finder = new Finder();

        $this->storage->deleteObject('/static/');
        $this->io->note('Properly deleted old assets');

        $this->io->note('Uploading build assets...');
        $this->handleAssetDirectory($finder, 'build');

        $this->io->note('Uploading bundles assets...');
        $this->handleAssetDirectory($finder, 'bundles');

        return Command::SUCCESS;
    }

    private function handleAssetDirectory(Finder $finder, string $directory): void
    {
        $files = $finder->in([
            $this->rootDirectory.'/public/'.$directory,
        ])->files();

        foreach ($files->getIterator() as $staticAsset) {
            /** @var SplFileInfo $staticAsset */
            $path = $staticAsset->getRealPath();
            $handler = fopen(strval($path), 'rb');
            $this->storage->uploadFile('static/'.$directory.'/'.$staticAsset->getRelativePathname(), $handler);
            /* @phpstan-ignore-next-line */
            fclose($handler);
        }
    }
}

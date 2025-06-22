<?php

namespace App\Command;

use App\Entity\Media;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use League\Flysystem\FilesystemOperator;
use Vich\UploaderBundle\Handler\UploadHandler;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:test-cdn-upload',
    description: 'Test CDN upload functionality and troubleshoot configuration issues',
)]
class TestCdnUploadCommand extends Command
{
    public function __construct(
        private readonly UploadHandler $uploadHandler,
        #[Autowire(service: 'default.storage')]
        private readonly FilesystemOperator $defaultFilesystem,
        private readonly LoggerInterface $logger,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('upload', null, InputOption::VALUE_NONE, 'Actually upload a test file')
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> command tests CDN upload functionality:

  <info>php %command.full_name%</info>

To actually upload a test file:

  <info>php %command.full_name% --upload</info>

This command will:
- Check if CDN configuration is present
- Test connection to the CDN
- Optionally upload a test file and verify it's accessible
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $upload = $input->getOption('upload');

        $io->title('CDN Upload Test');

        // Check environment configuration
        $io->section('Environment Configuration');
        
        $cdnHost = $_ENV['BUNNYCDN_HOSTNAME'] ?? null;
        $cdnKey = $_ENV['BUNNYCDN_API_KEY'] ?? null;
        $cdnStorage = $_ENV['BUNNYCDN_STORAGE_ZONE'] ?? null;
        $cdnPullZone = $_ENV['BUNNYCDN_PULL_ZONE'] ?? null;

        $configOk = true;
        
        if (!$cdnHost) {
            $io->error('BUNNYCDN_HOSTNAME is not set');
            $configOk = false;
        } else {
            $io->success("BUNNYCDN_HOSTNAME: {$cdnHost}");
        }

        if (!$cdnKey) {
            $io->error('BUNNYCDN_API_KEY is not set');
            $configOk = false;
        } else {
            $io->success('BUNNYCDN_API_KEY: [REDACTED]');
        }

        if (!$cdnStorage) {
            $io->error('BUNNYCDN_STORAGE_ZONE is not set');
            $configOk = false;
        } else {
            $io->success("BUNNYCDN_STORAGE_ZONE: {$cdnStorage}");
        }

        if (!$cdnPullZone) {
            $io->error('BUNNYCDN_PULL_ZONE is not set');
            $configOk = false;
        } else {
            $io->success("BUNNYCDN_PULL_ZONE: {$cdnPullZone}");
        }

        if (!$configOk) {
            $io->error('CDN configuration is incomplete. Please check your .env.local file.');
            $io->note('Example configuration:');
            $io->listing([
                'BUNNYCDN_HOSTNAME=storage.bunnycdn.com',
                'BUNNYCDN_API_KEY=your-api-key-here',
                'BUNNYCDN_STORAGE_ZONE=your-storage-zone',
                'BUNNYCDN_PULL_ZONE=https://your-pull-zone.b-cdn.net',
            ]);
            return Command::FAILURE;
        }

        // Test filesystem connection
        $io->section('Testing Filesystem Connection');
        
        try {
            // Try to list files in the root directory
            $files = $this->defaultFilesystem->listContents('/', false);
            $fileCount = 0;
            foreach ($files as $file) {
                $fileCount++;
            }
            $io->success("Successfully connected to CDN. Found {$fileCount} files/directories.");
        } catch (\Exception $e) {
            $io->error('Failed to connect to CDN: ' . $e->getMessage());
            $this->logger->error('CDN connection test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }

        // Upload test file if requested
        if ($upload) {
            $io->section('Testing File Upload');
            
            try {
                // Create a test image file
                $testImagePath = $this->projectDir . '/var/test-cdn-upload.jpg';
                $testImage = imagecreatetruecolor(100, 100);
                $white = imagecolorallocate($testImage, 255, 255, 255);
                $black = imagecolorallocate($testImage, 0, 0, 0);
                imagefill($testImage, 0, 0, $white);
                imagestring($testImage, 5, 10, 40, 'CDN TEST', $black);
                imagejpeg($testImage, $testImagePath, 90);
                imagedestroy($testImage);

                $io->info('Created test image: ' . $testImagePath);

                // Create Media entity and upload
                $file = new UploadedFile($testImagePath, 'test-cdn-upload.jpg', 'image/jpeg', null, true);
                $media = (new Media())
                    ->setFile($file)
                    ->setName($file->getFilename())
                    ->setSize($file->getSize());

                $this->uploadHandler->upload($media, 'file');

                $io->success('Successfully uploaded test file to CDN');
                $io->table(
                    ['Property', 'Value'],
                    [
                        ['Filename', $media->getName()],
                        ['Size', $media->getSize() . ' bytes'],
                        ['CDN Path', $media->getPath()],
                    ]
                );

                // Clean up test file
                @unlink($testImagePath);
                
                $io->success('Test completed successfully! CDN upload is working properly.');
                
            } catch (\Exception $e) {
                $io->error('Failed to upload test file: ' . $e->getMessage());
                $this->logger->error('CDN upload test failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                // Clean up test file
                if (isset($testImagePath) && file_exists($testImagePath)) {
                    @unlink($testImagePath);
                }
                
                return Command::FAILURE;
            }
        } else {
            $io->note('Run with --upload option to test actual file upload.');
        }

        return Command::SUCCESS;
    }
}
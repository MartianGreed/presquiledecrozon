<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Panther\Client;

trait BaseE2ETrait
{
    protected Client $client;

    protected static ?Application $application = null;

    protected static bool $databaseCreated = false;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        if (! self::$databaseCreated) {
            self::createAndSetupDatabase();
            self::$databaseCreated = true;
        }
    }

    protected function setUp(): void
    {
        // Use the installed chromedriver
        $_SERVER['PANTHER_CHROME_DRIVER_BINARY'] = dirname(__DIR__, 2) . '/drivers/chromedriver';

        $chromeOptions = [
            '--headless=new',  // Use new headless mode
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--disable-software-rasterizer',
            '--window-size=1920,1080',
            '--disable-web-security',
            '--allow-insecure-localhost',
            '--remote-debugging-port=9222',
        ];

        $this->client = static::createPantherClient([
            'browser' => static::CHROME,
            'external_base_uri' => $_SERVER['PANTHER_EXTERNAL_BASE_URI'] ?? 'http://127.0.0.1:9080',
            'chromedriver_arguments' => [
                '--silent',
                '--log-level=3',
            ],
            'capabilities' => [
                'goog:chromeOptions' => [
                    'args' => $chromeOptions,
                ],
            ],
        ]);

        self::clearDatabase();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private static function createAndSetupDatabase(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();

        self::$application = new Application($kernel);
        self::$application->setAutoExit(false);

        self::runCommand([
            'command' => 'doctrine:database:drop',
            '--if-exists' => true,
            '--force' => true,
        ]);
        self::runCommand([
            'command' => 'doctrine:database:create',
        ]);
        self::runCommand([
            'command' => 'doctrine:schema:create',
        ]);
    }

    private static function clearDatabase(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();

        /** @var ManagerRegistry $doctrine */
        $doctrine = $kernel->getContainer()->get('doctrine');
        /** @var EntityManagerInterface $em */
        $em = $doctrine->getManager();
        $connection = $em->getConnection();

        // PostgreSQL syntax for disabling foreign key checks
        $tables = $connection->createSchemaManager()->listTableNames();
        foreach ($tables as $table) {
            if ('doctrine_migration_versions' !== $table) {
                $connection->executeStatement('TRUNCATE TABLE ' . $table . ' CASCADE');
            }
        }
    }

    /**
     * @param array<string, mixed> $input
     */
    private static function runCommand(array $input): void
    {
        if (! self::$application instanceof \Symfony\Bundle\FrameworkBundle\Console\Application) {
            throw new \RuntimeException('Application not initialized');
        }

        $input = new ArrayInput($input);
        $output = new NullOutput();

        self::$application->run($input, $output);
    }

    protected function createTestUser(string $email, string $password): void
    {
        $this->client->request('GET', '/creer-mon-compte');
        $this->client->submitForm('Je crée mon compte', [
            'register_user[email]' => $email,
            'register_user[password][first]' => $password,
            'register_user[password][second]' => $password,
        ]);
    }

    protected function login(string $email, string $password): void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm('Je me connecte', [
            'email' => $email,
            'password' => $password,
        ]);
    }

    protected function waitForElement(string $selector, int $timeout = 5): void
    {
        $this->client->waitFor($selector, $timeout);
    }

    protected function assertPageContainsText(string $text): void
    {
        $this->assertStringContainsString($text, $this->client->getCrawler()->text());
    }

    protected function assertCurrentUrlIs(string $expectedPath): void
    {
        if ($expectedPath === '') {
            throw new \InvalidArgumentException('Expected path cannot be empty');
        }
        $currentUrl = $this->client->getCurrentURL();
        $this->assertStringEndsWith($expectedPath, $currentUrl);
    }
}


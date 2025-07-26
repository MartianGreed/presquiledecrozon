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

        self::clearDatabase();
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
            '--ignore-certificate-errors',
        ];

        $this->client = static::createPantherClient([
            'browser' => static::CHROME,
            'port' => 8000,
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
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Called automatically when a test fails.
     */
    protected function onNotSuccessfulTest(\Throwable $t): never
    {
        $this->takeFailureScreenshot();
        parent::onNotSuccessfulTest($t);
    }

    /**
     * Take a screenshot when a test fails.
     */
    protected function takeFailureScreenshot(): void
    {
        if (! isset($this->client)) {
            echo "\nNo client available for screenshot\n";

            return;
        }

        try {
            $screenshotDir = $_ENV['PANTHER_ERROR_SCREENSHOT_DIR'] ?? './var/error-screenshots';
            if (! is_string($screenshotDir)) {
                $screenshotDir = './var/error-screenshots';
            }

            // Create directory if it doesn't exist
            if (! is_dir($screenshotDir)) {
                mkdir($screenshotDir, 0777, true);
            }

            // Generate filename with test class and method name
            $testClass = (new \ReflectionClass($this))->getShortName();
            $testMethod = 'unknown';

            // Try to get the test method name from debug backtrace
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
            foreach ($backtrace as $frame) {
                if (str_starts_with($frame['function'], 'test')) {
                    $testMethod = $frame['function'];
                    break;
                }
            }

            $timestamp = date('Y-m-d_H-i-s');
            $filename = sprintf(
                '%s/%s_%s_%s.png',
                rtrim($screenshotDir, '/'),
                $testClass,
                $testMethod,
                $timestamp
            );

            // Take the screenshot
            $this->client->takeScreenshot($filename);

            // Also save the HTML source for debugging
            $htmlFilename = str_replace('.png', '.html', $filename);
            file_put_contents($htmlFilename, $this->client->getPageSource());
        } catch (\Exception $e) {
        }
    }

    private static function createAndSetupDatabase(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();

        self::$application = new Application($kernel);
        self::$application->setAutoExit(false);
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
        if (! self::$application instanceof Application) {
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
        if ('' === $expectedPath) {
            throw new \InvalidArgumentException('Expected path cannot be empty');
        }
        $currentUrl = $this->client->getCurrentURL();
        $this->assertStringEndsWith($expectedPath, $currentUrl);
    }

    /**
     * Manually take a screenshot for debugging.
     */
    protected function takeDebugScreenshot(string $name = ''): void
    {
        if (! isset($this->client)) {
            return;
        }

        try {
            $screenshotDir = $_ENV['PANTHER_ERROR_SCREENSHOT_DIR'] ?? './var/error-screenshots';
            if (! is_string($screenshotDir)) {
                $screenshotDir = './var/error-screenshots';
            }

            // Create directory if it doesn't exist
            if (! is_dir($screenshotDir)) {
                mkdir($screenshotDir, 0777, true);
            }

            // Generate filename
            $testClass = (new \ReflectionClass($this))->getShortName();
            $timestamp = date('Y-m-d_H-i-s');
            $suffix = '' !== $name && '0' !== $name ? "_$name" : '';
            $filename = sprintf(
                '%s/%s_debug%s_%s.png',
                rtrim($screenshotDir, '/'),
                $testClass,
                $suffix,
                $timestamp
            );

            // Take the screenshot
            $this->client->takeScreenshot($filename);
        } catch (\Exception $e) {
        }
    }
}

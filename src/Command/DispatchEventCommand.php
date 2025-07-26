<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand('app:debug:dispatch-event')]
final class DispatchEventCommand extends Command
{
    private const MESSAGE_FQN_PREFIX = 'App\\Message\\';

    private SymfonyStyle $io;
    /** @var class-string */
    private string $messageClass;
    private QuestionHelper $helper;

    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addArgument('class', InputArgument::REQUIRED, 'Classname of the message you want to dispatch');
    }

    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
        $classArg = $input->getArgument('class');
        if (!is_string($classArg)) {
            throw new \RuntimeException('Class argument must be a string');
        }
        /** @var class-string $message */
        $message = $classArg;
        $this->messageClass = $message;

        // PHPStan hack to ensure type
        $helper = $this->getHelper('question');
        if ($helper instanceof QuestionHelper) {
            $this->helper = $helper;
        }

        $this->io->title(sprintf('Dispatching event of type : %s', $this->messageClass));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->messageExists($this->messageClass)) {
            $this->io->error(sprintf('Message named %s does not exists', $this->messageClass));

            return Command::FAILURE;
        }

        $constructorArgsName = $this->getMessageConstructorArgNames($this->messageClass);
        $constructorValues = [];
        foreach ($constructorArgsName as $key => $arg) {
            $constructorValues[$arg] = $this->askArgumentValue($input, $output, $arg);
        }

        // @phpstan-ignore-next-line
        $reflection = new \ReflectionClass($this->getClassFQN($this->messageClass));

        $messageToDispatch = $reflection->newInstanceArgs(array_values($constructorValues));

        $this->messageBus->dispatch($messageToDispatch);

        $this->io->write('Message %s has been dispatched with following arguments :');
        $this->io->listing($constructorValues);

        return Command::SUCCESS;
    }

    private function askArgumentValue(InputInterface $input, OutputInterface $output, string $arg): string
    {
        $question = new Question(sprintf('Please set the value for : $%s  ->  ', $arg));

        $answer = $this->helper->ask($input, $output, $question);
        if (!is_string($answer) && !is_numeric($answer)) {
            throw new \RuntimeException(sprintf('Answer for %s must be a string or numeric value', $arg));
        }

        return (string) $answer;
    }

    /** @return array<int, string> */
    private function getMessageConstructorArgNames(string $messageClass): array
    {
        // @phpstan-ignore-next-line
        $reflectionClass = new \ReflectionClass($this->getClassFQN($messageClass));
        $constructor = $reflectionClass->getMethod('__construct');

        return array_map(static fn (\ReflectionParameter $p) => $p->getName(), $constructor->getParameters());
    }

    private function messageExists(string $messageClass): bool
    {
        return class_exists($this->getClassFQN($messageClass));
    }

    private function getClassFQN(string $messageClass): string
    {
        return self::MESSAGE_FQN_PREFIX.$messageClass;
    }
}

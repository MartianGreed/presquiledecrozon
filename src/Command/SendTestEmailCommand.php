<?php

namespace App\Command;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

#[AsCommand('app:debug:email')]
final class SendTestEmailCommand extends Command
{
    private SymfonyStyle $io;

    private string $emailTarget;

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $emailSender,
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'Classname of the message you want to dispatch');
    }

    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);

        $emailArg = $input->getArgument('email');
        if (! is_string($emailArg)) {
            throw new \RuntimeException('Email argument must be a string');
        }
        $this->emailTarget = $emailArg;

        $this->io->title(sprintf('Sending test email to : %s', $this->emailTarget));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (new TemplatedEmail())
            ->from($this->emailSender)
            ->to(new Address($this->emailTarget))
            ->subject('Votre inscription a bien été prise en compte !')
            ->htmlTemplate('emails/user_registered.html.twig')
        ;

        $this->mailer->send($email);

        return Command::SUCCESS;
    }
}

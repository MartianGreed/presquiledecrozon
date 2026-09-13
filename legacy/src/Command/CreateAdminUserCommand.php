<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand('app:user:create-admin')]
final class CreateAdminUserCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $manager,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->io->title('Create new admin user.');
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'Email of the newborn admin user');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        if (! is_string($email)) {
            throw new \RuntimeException('Email argument must be a string');
        }

        $user = $this->userRepository->findOneBy([
            'email' => $email,
        ]);
        if (null !== $user) {
            $this->io->error('Impossible de recréer un compte pour cet utilisateur.');

            return self::FAILURE;
        }
        $password = $this->io->askHidden('Tapez votre mot de passe');
        if (! is_string($password)) {
            throw new \RuntimeException('Password must be a string');
        }

        $user = new User();
        $user
            ->setEmail($email)
            ->setPassword($this->hasher->hashPassword($user, $password))
            ->setRoles(['ROLE_USER', 'ROLE_ADMIN'])
        ;

        $this->manager->persist($user);
        $this->manager->flush();

        $this->io->success(sprintf('L\'utilisateur à bien été créé pour l\'email : %s', $user->getEmail()));

        return self::SUCCESS;
    }
}

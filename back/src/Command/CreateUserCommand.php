<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Créer un nouvel utilisateur pour tester le système de login',
)]
class CreateUserCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'Nom d\'utilisateur')
            ->addArgument('password', InputArgument::REQUIRED, 'Mot de passe')
            ->addOption('admin', 'a', InputOption::VALUE_NONE, 'Donner les droits administrateur à l\'utilisateur');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $isAdmin = $input->getOption('admin');

        // Vérifier si l'utilisateur existe déjà
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
        if ($existingUser) {
            $io->error('Un utilisateur avec ce nom existe déjà !');
            return Command::FAILURE;
        }

        // Déterminer les rôles
        $roles = ['ROLE_USER'];
        if ($isAdmin) {
            $roles[] = 'ROLE_ADMIN';
        }

        // Créer un nouvel utilisateur
        $user = new User();
        $user->setUsername($username);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setRoles($roles);
        $user->setActif(true);
        $user->setDateCreation(new \DateTime());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('Utilisateur créé avec succès !');
        $io->table(['ID', 'Username', 'Rôles', 'Actif', 'Date de création'], [
            [
                $user->getId(), 
                $user->getUsername(), 
                implode(', ', $user->getRoles()),
                $user->isActif() ? 'Oui' : 'Non', 
                $user->getDateCreation()->format('Y-m-d H:i:s')
            ]
        ]);

        return Command::SUCCESS;
    }
}

<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

#[Route('/api', name: 'app_auth_')]
final class AuthController extends AbstractController
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private JWTTokenManagerInterface $jwtManager;
    private ValidatorInterface $validator;
    private EntityManagerInterface $entityManager;

    public function __construct(
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager
    ) {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->jwtManager = $jwtManager;
        $this->validator = $validator;
        $this->entityManager = $entityManager;
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        // Récupération des données JSON ou form-data
        $data = json_decode($request->getContent(), true) ?: $request->request->all();
        
        if(!isset($data['username']) || !isset($data['password'])) {
            return $this->json([
                'message' => 'Identifiants manquants'
            ], Response::HTTP_BAD_REQUEST);
        }

        $username = $data['username'];
        $password = $data['password'];

        // Recherche de l'utilisateur
        $user = $this->userRepository->findOneBy(['username' => $username]);
        
        if (!$user) {
            return $this->json([
                'message' => 'Utilisateur non trouvé'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Vérification si l'utilisateur est actif
        if (!$user->isActif()) {
            return $this->json([
                'message' => 'Compte utilisateur désactivé'
            ], Response::HTTP_FORBIDDEN);
        }

        // Vérification du mot de passe avec le hasher Symfony
        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            return $this->json([
                'message' => 'Identifiants invalides'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Génération du token JWT avec une durée de vie de 1 heure
        $token = $this->jwtManager->create($user);
        
        // Calcul de la date d'expiration (1 heure)
        $expiresAt = new \DateTime('+1 hour');

        return $this->json([
            'message' => 'Connexion réussie',
            'token' => $token,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'roles' => $user->getRoles(),
                'actif' => $user->isActif(),
                'date_creation' => $user->getDateCreation()?->format('Y-m-d H:i:s')
            ]
        ], Response::HTTP_OK);
    }


    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?: $request->request->all();

        if (!isset($data['username']) || !isset($data['password'])) {
            return $this->json([
                'message' => 'Identifiants manquants'
            ], Response::HTTP_BAD_REQUEST);
        }

        $username = $data['username'];
        $password = $data['password'];
        $errors = [];

        // Validation du nom d'utilisateur
        $usernameViolations = $this->validator->validate($username, [
            new Assert\NotBlank(),
            new Assert\Length(['min' => 3, 'max' => 50]),
            new Assert\Regex([
                'pattern' => '/^[a-zA-Z0-9_]+$/',
                'message' => 'Le nom d\'utilisateur ne doit contenir que des lettres, des chiffres et des tirets bas.'
            ])
        ]);
        foreach ($usernameViolations as $violation) {
            $errors['username'][] = $violation->getMessage();
        }

        // Validation du mot de passe
        $passwordViolations = $this->validator->validate($password, [
            new Assert\NotBlank(),
            new Assert\Length(['min' => 6, 'max' => 255]),
            new Assert\Regex([
                'pattern' => '/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
                'message' => 'Le mot de passe doit contenir au moins 6 caractères avec au moins une lettre, un chiffre et un caractère spécial (@$!%*?&).'
            ])
        ]);
        foreach ($passwordViolations as $violation) {
            $errors['password'][] = $violation->getMessage();
        }

        if (!empty($errors)) {
            return $this->json([
                'message' => 'Erreur de validation',
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérifie si l'utilisateur existe déjà
        $existingUser = $this->userRepository->findOneBy(['username' => $username]);
        if ($existingUser) {
            return $this->json([
                'message' => 'Ce nom d\'utilisateur est déjà pris'
            ], Response::HTTP_CONFLICT);
        }

        // Création de l'utilisateur
        $user = new User();
        $user->setUsername($username);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setRoles(['ROLE_USER']);
        $user->setActif(true);
        $user->setDateCreation(new \DateTime());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $token = $this->jwtManager->create($user);
        $expiresAt = new \DateTime('+1 hour');

        return $this->json([
            'message' => 'Utilisateur créé avec succès',
            'token' => $token,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'roles' => $user->getRoles(),
                'actif' => $user->isActif(),
                'date_creation' => $user->getDateCreation()?->format('Y-m-d H:i:s')
            ]
        ], Response::HTTP_CREATED);
    }

}

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

#[Route('/api', name: 'app_auth_')]
final class AuthController extends AbstractController
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private JWTTokenManagerInterface $jwtManager;
    private ValidatorInterface $validator;

    public function __construct(
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager,
        ValidatorInterface $validator
    ) {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->jwtManager = $jwtManager;
        $this->validator = $validator;
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
}

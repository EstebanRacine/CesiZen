<?php

namespace App\Controller;

use App\Controller\AbstractApiController;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;

#[Route('/api/user', name: 'api_user_')]
final class UserController extends AbstractApiController
{

    #[Route('/{id}', name: 'get_by_id', methods: ['GET'])]
    #[OA\Tag(name: 'User')]
    #[OA\Get(
        summary: 'Get User by ID',
        description: 'Retrouve un utilisateur par son ID',
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID de l\'utilisateur',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Utilisateur trouvé',
        content: new OA\JsonContent(ref: '#/components/schemas/User')
    )]
    public function getById(int $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse([
                'message' => 'Utilisateur non trouvé',
                'code' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json($user, Response::HTTP_OK, [], ['groups' => ['user:read']]);

    }

    #[Route('/', name: 'create', methods: ['POST'])]
    #[OA\Tag(name: 'User')]
    #[OA\Post(
        summary: 'Create a new User',
        description: 'Crée un nouvel utilisateur'
    )]
    #[OA\RequestBody(
        description: 'Les données du type de l\'utilisateur à créer',
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'username', 
                    type: 'string', 
                    description: 'Username de l\'utilisateur',
                    example: 'john_doe'
                ),
                new OA\Property(
                    property: 'password', 
                    type: 'string', 
                    description: 'Mot de passe de l\'utilisateur',
                    example: 'password123'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: 'Utilisateur créé avec succès',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'id',
                    type: 'integer',
                    description: 'ID de l\'utilisateur créé',
                    example: 1
                ),
                new OA\Property(
                    property: 'username',
                    type: 'string',
                    description: 'Username de l\'utilisateur créé',
                    example: 'john_doe'
                ),
                new OA\Property(
                    property: 'roles',
                    type: 'array',
                    items: new OA\Items(type: 'string'),
                    description: 'Rôles de l\'utilisateur',
                    example: ['ROLE_USER']
                ),
                new OA\Property(
                    property: 'token',
                    type: 'string',
                    description: 'Token d\'authentification de l\'utilisateur',
                    example: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9.dyt0CoTl4WoVjAHI9Q_CwSKhl6d_9rhM3NrXuJttkao'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Requête invalide',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    description: 'Message d\'erreur',
                    example: 'Username et mot de passe requis'
                ),
                new OA\Property(
                    property: 'code',
                    type: 'integer',
                    description: 'Code d\'erreur HTTP',
                    example: Response::HTTP_BAD_REQUEST
                ),
            ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_CONFLICT,
        description: 'Conflit de données',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    description: 'Message d\'erreur',
                    example: 'Nom d\'utilisateur déjà pris'
                ),
                new OA\Property(
                    property: 'code',
                    type: 'integer',
                    description: 'Code d\'erreur HTTP',
                    example: Response::HTTP_CONFLICT
                ),
            ]
        )
    )]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager
    ): Response {
        $data = $this->extractAllRequestData($request);

        if (!isset($data['username']) || !isset($data['password'])) {
            return new JsonResponse([
                'message' => 'Username et mot de passe requis',
                'code' => Response::HTTP_BAD_REQUEST
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($userRepository->findOneBy(['username' => $data['username']])) {
            return new JsonResponse([
                'message' => 'Nom d\'utilisateur déjà pris',
                'code' => Response::HTTP_CONFLICT
            ], Response::HTTP_CONFLICT);
        }

        if (strlen($data['password']) < 8 || !preg_match('/[A-Z]/', $data['password']) || !preg_match('/[0-9]/', $data['password'])) {
            return new JsonResponse([
                'message' => 'Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre',
                'code' => Response::HTTP_BAD_REQUEST
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setUsername($data['username']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        $user->setRoles(['ROLE_USER']);
        $user->setActif(true);
        $user->setDateCreation(new \DateTime());

        $em->persist($user);
        $em->flush();

        $token = $jwtManager->create($user);

        return new JsonResponse([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
            'token' => $token,
        ], Response::HTTP_CREATED);
    }


    #[Route('/change-username', name: 'change_username', methods: ['POST'])]
    #[OA\Tag(name: 'User')]
    #[OA\Post(
        summary: 'Change Username',
        description: 'Change le nom d\'utilisateur d\'un utilisateur existant'
    )]
    #[OA\RequestBody(
        description: 'Les données pour changer le nom d\'utilisateur',
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'id',
                    type: 'integer',
                    description: 'ID de l\'utilisateur',
                    example: 1
                ),
                new OA\Property(
                    property: 'username',
                    type: 'string',
                    description: 'Nouveau nom d\'utilisateur',
                    example: 'new_username'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Nom d\'utilisateur mis à jour avec succès',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    description: 'Message de succès',
                    example: 'Nom d\'utilisateur mis à jour avec succès'
                ),
                new OA\Property(
                    property: 'code',
                    type: 'integer',
                    description: 'Code HTTP',
                    example: Response::HTTP_OK
                ),
            ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Requête invalide',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    description: 'Message d\'erreur',
                    example: 'ID de l\'utilisateur et nouveau nom d\'utilisateur requis'
                ),
                new OA\Property(
                    property: 'code',
                    type: 'integer',
                    description: 'Code d\'erreur HTTP',
                    example: Response::HTTP_BAD_REQUEST
                ),
            ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Utilisateur non trouvé',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    description: 'Message d\'erreur',
                    example: 'Utilisateur non trouvé'
                ),
                new OA\Property(
                    property: 'code',
                    type: 'integer',
                    description: 'Code d\'erreur HTTP',
                    example: Response::HTTP_NOT_FOUND
                ),
            ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_CONFLICT,
        description: 'Nom d\'utilisateur déjà pris',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    description: 'Message d\'erreur',
                    example: 'Nom d\'utilisateur déjà pris'
                ),
                new OA\Property(
                    property: 'code',
                    type: 'integer',
                    description: 'Code d\'erreur HTTP',
                    example: Response::HTTP_CONFLICT
                ),
            ]
        )
    )]
    public function changeUsername(Request $request, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        $data = $this->extractAllRequestData($request);
        $userId = $data['id'] ?? null;
        $newUsername = $data['username'] ?? null;

        if (!$userId || !$newUsername) {
            return new JsonResponse([
                'message' => 'ID de l\'utilisateur et nouveau nom d\'utilisateur requis',
                'code' => Response::HTTP_BAD_REQUEST
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->find($userId);

        if (!$user) {
            return new JsonResponse([
                'message' => 'Utilisateur non trouvé',
                'code' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }

        if ($userRepository->findOneBy(['username' => $newUsername])) {
            return new JsonResponse([
                'message' => 'Nom d\'utilisateur déjà pris',
                'code' => Response::HTTP_CONFLICT
            ], Response::HTTP_CONFLICT);
        }

        $user->setUsername($newUsername);
        $em->flush();

        return new JsonResponse([
            'message' => 'Nom d\'utilisateur mis à jour avec succès',
            'code' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    // Utilise le token du user pour trouver son ID et reinitialiser son password
    #[Route('/reset-password', name: 'reset_password', methods: ['POST'])]
    #[OA\Tag(name: 'User')]
    #[OA\Post(
        summary: 'Reset Password',
        description: 'Réinitialise le mot de passe de l\'utilisateur connecté'
    )]
    #[OA\RequestBody(
        description: 'Les données pour réinitialiser le mot de passe',
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'old_password',
                    type: 'string',
                    description: 'Ancien mot de passe',
                    example: 'new_password123'
                ),
                new OA\Property(
                    property: 'new_password',
                    type: 'string',
                    description: 'Nouveau mot de passe',
                    example: 'new_password123'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Mot de passe réinitialisé avec succès',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    description: 'Message de succès',
                    example: 'Mot de passe réinitialisé avec succès'
                ),
                new OA\Property(
                    property: 'code',
                    type: 'integer',
                    description: 'Code HTTP',
                    example: Response::HTTP_OK
                ),
            ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Requête invalide',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    description: 'Message d\'erreur',
                    example: 'Ancien mot de passe et nouveau mot de passe requis'
                ),
                new OA\Property(
                    property: 'code',
                    type: 'integer',
                    description: 'Code d\'erreur HTTP',
                    example: Response::HTTP_BAD_REQUEST
                ),
            ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Non autorisé',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    description: 'Message d\'erreur',
                    example: 'Non autorisé'
                ),
                new OA\Property(
                    property: 'code',
                    type: 'integer',
                    description: 'Code d\'erreur HTTP',
                    example: Response::HTTP_UNAUTHORIZED
                ),
            ]
        )
    )]
    public function resetPassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
    ): Response {
        $data = $this->extractAllRequestData($request);
        $oldPassword = $data['old_password'] ?? null;
        $newPassword = $data['new_password'] ?? null;

        if (!$oldPassword || !$newPassword) {
            return new JsonResponse([
                'message' => 'Ancien mot de passe et nouveau mot de passe requis',
                'code' => Response::HTTP_BAD_REQUEST
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse([
                'message' => 'Non autorisé',
                'code' => Response::HTTP_UNAUTHORIZED
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$passwordHasher->isPasswordValid($user, $oldPassword)) {
            return new JsonResponse([
                'message' => 'Ancien mot de passe incorrect',
                'code' => Response::HTTP_UNAUTHORIZED
            ], Response::HTTP_UNAUTHORIZED);
        }

        if(!preg_match('/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/', $newPassword)) {
            return new JsonResponse([
                'message' => 'Le mot de passe doit contenir au moins 6 caractères, une lettre, un chiffre et un caractère spécial',
                'code' => Response::HTTP_BAD_REQUEST
            ], Response::HTTP_BAD_REQUEST);
        }

        $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
        $em->flush();

        return new JsonResponse([
            'message' => 'Mot de passe réinitialisé avec succès',
            'code' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    #[Route('/admin/reset-password', name: 'admin_reset_password', methods: ['POST'])]
    #[OA\Tag(name: 'User')]
    #[OA\Post(
        summary: 'Admin Reset Password',
        description: 'Réinitialise le mot de passe d\'un utilisateur par un administrateur'
    )]
    #[OA\RequestBody(
        description: 'Les données pour réinitialiser le mot de passe d\'un utilisateur',
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'id',
                    type: 'integer',
                    description: 'ID de l\'utilisateur',
                    example: 1
                ),
                new OA\Property(
                    property: 'new_password',
                    type: 'string',
                    description: 'Nouveau mot de passe',
                    example: 'new_password123'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Mot de passe réinitialisé avec succès',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    description: 'Message de succès',
                    example: 'Mot de passe réinitialisé avec succès'
                ),
                new OA\Property(
                    property: 'code',
                    type: 'integer',
                    description: 'Code HTTP',
                    example: Response::HTTP_OK
                ),
            ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Requête invalide',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    description: 'Message d\'erreur',
                    example: 'ID de l\'utilisateur et nouveau mot de passe requis'
                ),
                new OA\Property(
                    property: 'code',
                    type: 'integer',
                    description: 'Code d\'erreur HTTP',
                    example: Response::HTTP_BAD_REQUEST
                ),
            ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Utilisateur non trouvé',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    description: 'Message d\'erreur',
                    example: 'Utilisateur non trouvé'
                ),
                new OA\Property(
                    property: 'code',
                    type: 'integer',
                    description: 'Code d\'erreur HTTP',
                    example: Response::HTTP_NOT_FOUND
                ),
            ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_CONFLICT,
        description: 'Mot de passe invalide',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    description: 'Message d\'erreur',
                    example: 'Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre'
                ),
                new OA\Property(
                    property: 'code',
                    type: 'integer',
                    description: 'Code d\'erreur HTTP',
                    example: Response::HTTP_BAD_REQUEST
                ),
            ]
        )
    )]
    public function adminResetPassword(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): Response {
        $data = $this->extractAllRequestData($request);
        $userId = $data['id'] ?? null;
        $newPassword = $data['new_password'] ?? null;

        if (!$userId || !$newPassword) {
            return new JsonResponse([
                'message' => 'ID de l\'utilisateur et nouveau mot de passe requis',
                'code' => Response::HTTP_BAD_REQUEST
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = $userRepository->find($userId);

        if (!$user) {
            return new JsonResponse([
                'message' => 'Utilisateur non trouvé',
                'code' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }

        if (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/', $newPassword)) {
            return new JsonResponse([
            'message' => 'Le mot de passe doit contenir au moins 6 caractères, une lettre, un chiffre et un caractère spécial',
            'code' => Response::HTTP_BAD_REQUEST
            ], Response::HTTP_BAD_REQUEST);
        }
        

        $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
        $em->flush();

        return new JsonResponse([
            'message' => 'Mot de passe réinitialisé avec succès',
            'code' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    #[Route('/admin/list', name: 'admin_get_all', methods: ['GET'])]
    #[OA\Tag(name: 'User')]
    #[OA\Get(
        summary: 'Get All Users (Admin)',
        description: 'Récupère tous les utilisateurs (accès admin requis)'
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Liste des utilisateurs',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/User')
        )
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Non autorisé'
    )]
    public function adminGetAllUsers(UserRepository $userRepository): Response
    {
        // Note: La vérification des droits admin devrait être faite par le système de sécurité
        $users = $userRepository->findAll();

        return $this->json($users, Response::HTTP_OK, [], ['groups' => ['user:read']]);
    }

    #[Route('/admin/create', name: 'admin_create', methods: ['POST'])]
    #[OA\Tag(name: 'User')]
    #[OA\Post(
        summary: 'Create User (Admin)',
        description: 'Crée un utilisateur via l\'interface admin'
    )]
    #[OA\RequestBody(
        description: 'Les données de l\'utilisateur à créer',
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'username',
                    type: 'string',
                    description: 'Nom d\'utilisateur',
                    example: 'john_doe'
                ),
                new OA\Property(
                    property: 'password',
                    type: 'string',
                    description: 'Mot de passe',
                    example: 'Password123'
                ),
                new OA\Property(
                    property: 'roles',
                    type: 'array',
                    items: new OA\Items(type: 'string'),
                    description: 'Rôles de l\'utilisateur',
                    example: ['ROLE_USER']
                ),
                new OA\Property(
                    property: 'actif',
                    type: 'boolean',
                    description: 'Statut actif',
                    example: true
                ),
            ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: 'Utilisateur créé avec succès',
        content: new OA\JsonContent(ref: '#/components/schemas/User')
    )]
    public function adminCreateUser(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $data = $this->extractAllRequestData($request);

        if (!isset($data['username']) || !isset($data['password'])) {
            return new JsonResponse([
                'message' => 'Username et mot de passe requis',
                'code' => Response::HTTP_BAD_REQUEST
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier si l'utilisateur existe déjà
        if ($userRepository->findOneBy(['username' => $data['username']])) {
            return new JsonResponse([
                'message' => 'Nom d\'utilisateur déjà pris',
                'code' => Response::HTTP_CONFLICT
            ], Response::HTTP_CONFLICT);
        }

        // Valider le mot de passe
        if (strlen($data['password']) < 8 || !preg_match('/[A-Z]/', $data['password']) || !preg_match('/[0-9]/', $data['password'])) {
            return new JsonResponse([
                'message' => 'Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre',
                'code' => Response::HTTP_BAD_REQUEST
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setUsername($data['username']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        
        // Gestion simplifiée des rôles : tous ont ROLE_USER, certains ont aussi ROLE_ADMIN
        $roles = ['ROLE_USER'];
        if (isset($data['isAdmin']) && $data['isAdmin']) {
            $roles[] = 'ROLE_ADMIN';
        }
        $user->setRoles($roles);
        
        $user->setActif($data['actif'] ?? true);
        $user->setDateCreation(new \DateTime());

        $em->persist($user);
        $em->flush();

        return $this->json($user, Response::HTTP_CREATED, [], ['groups' => ['user:read']]);
    }

    #[Route('/admin/update/{id}', name: 'admin_update', methods: ['POST'])]
    #[OA\Tag(name: 'User')]
    #[OA\Post(
        summary: 'Update User (Admin)',
        description: 'Met à jour un utilisateur via l\'interface admin'
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID de l\'utilisateur',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        description: 'Les données à mettre à jour',
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'username',
                    type: 'string',
                    description: 'Nouveau nom d\'utilisateur'
                ),
                new OA\Property(
                    property: 'roles',
                    type: 'array',
                    items: new OA\Items(type: 'string'),
                    description: 'Nouveaux rôles'
                ),
            ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Utilisateur mis à jour avec succès',
        content: new OA\JsonContent(ref: '#/components/schemas/User')
    )]
    public function adminUpdateUser(
        int $id,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse([
                'message' => 'Utilisateur non trouvé',
                'code' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }

        $data = $this->extractAllRequestData($request);

        // Mettre à jour le nom d'utilisateur si fourni
        if (isset($data['username'])) {
            // Vérifier si le nouveau nom d'utilisateur est déjà pris
            $existingUser = $userRepository->findOneBy(['username' => $data['username']]);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                return new JsonResponse([
                    'message' => 'Nom d\'utilisateur déjà pris',
                    'code' => Response::HTTP_CONFLICT
                ], Response::HTTP_CONFLICT);
            }
            $user->setUsername($data['username']);
        }

        // Mettre à jour les rôles si fournis
        if (isset($data['roles'])) {
            $user->setRoles($data['roles']);
        }

        $em->flush();

        return $this->json($user, Response::HTTP_OK, [], ['groups' => ['user:read']]);
    }

    #[Route('/admin/{id}', name: 'admin_delete', methods: ['DELETE'])]
    #[OA\Tag(name: 'User')]
    #[OA\Delete(
        summary: 'Delete User (Admin)',
        description: 'Supprime un utilisateur (suppression logique)'
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID de l\'utilisateur',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Utilisateur supprimé avec succès'
    )]
    public function adminDeleteUser(
        int $id,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse([
                'message' => 'Utilisateur non trouvé',
                'code' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }

        // Suppression logique
        $user->setDateSupression(new \DateTime());
        $user->setActif(false);

        $em->flush();

        return new JsonResponse([
            'message' => 'Utilisateur supprimé avec succès',
            'code' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    #[Route('/admin/toggle-status/{id}', name: 'admin_toggle_status', methods: ['POST'])]
    #[OA\Tag(name: 'User')]
    #[OA\Post(
        summary: 'Toggle User Status (Admin)',
        description: 'Active/désactive un utilisateur'
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID de l\'utilisateur',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Statut de l\'utilisateur modifié avec succès'
    )]
    public function adminToggleUserStatus(
        int $id,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse([
                'message' => 'Utilisateur non trouvé',
                'code' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }

        $user->setActif(!$user->isActif());
        $em->flush();

        return new JsonResponse([
            'message' => 'Statut de l\'utilisateur modifié avec succès',
            'actif' => $user->isActif(),
            'code' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    #[Route('/admin/update-roles/{id}', name: 'admin_update_roles', methods: ['POST'])]
    #[OA\Tag(name: 'User')]
    #[OA\Post(
        summary: 'Update User Roles (Admin)',
        description: 'Met à jour les rôles d\'un utilisateur'
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'ID de l\'utilisateur',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        description: 'Les nouveaux rôles',
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'roles',
                    type: 'array',
                    items: new OA\Items(type: 'string'),
                    description: 'Nouveaux rôles de l\'utilisateur',
                    example: ['ROLE_USER', 'ROLE_ADMIN']
                )
            ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Statut administrateur basculé avec succès'
    )]
    public function adminUpdateUserRoles(
        int $id,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        $user = $userRepository->find($id);

        if (!$user) {
            return new JsonResponse([
                'message' => 'Utilisateur non trouvé',
                'code' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }

        // Basculer le statut administrateur
        $currentRoles = $user->getRoles();
        $isCurrentlyAdmin = in_array('ROLE_ADMIN', $currentRoles);
        
        if ($isCurrentlyAdmin) {
            // Retirer ROLE_ADMIN (garder toujours ROLE_USER)
            $newRoles = ['ROLE_USER'];
        } else {
            // Ajouter ROLE_ADMIN (garder ROLE_USER)
            $newRoles = ['ROLE_USER', 'ROLE_ADMIN'];
        }
        
        $user->setRoles($newRoles);
        $em->flush();

        return new JsonResponse([
            'message' => 'Statut administrateur basculé avec succès',
            'roles' => $user->getRoles(),
            'isAdmin' => in_array('ROLE_ADMIN', $newRoles),
            'code' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

}

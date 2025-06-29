<?php

namespace App\Controller;

use App\Controller\AbstractApiController;
use App\Entity\CategorieEmotion;
use App\Repository\EmotionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use App\Entity\Emotion;
use Nelmio\ApiDocBundle\Attribute\Model;

#[Route('/api/emotion', name: 'app_emotion_')]
final class EmotionController extends AbstractApiController
{

    #[Route('/all', name: 'get_all', methods: ['GET'])]
    #[OA\Tag(name: 'Émotions')]
    #[OA\Get(
        summary: 'Récupérer toutes les émotions',
        description: 'Retourne une liste de toutes les émotions disponibles.',
    )]
    #[OA\Response(
        response: 200,
        description: 'Liste des émotions',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                ref: new Model(
                    type: Emotion::class,
                    groups: ['emotion:read']
                )
            )
        )
    )]
    public function getAll(EmotionRepository $emotionRepository): Response
    {
        $emotions = $emotionRepository->findAll();
        return $this->json($emotions, Response::HTTP_OK, [], ['groups' => 'emotion:read']);
    }

    #[Route('', name: 'get_all_active', methods: ['GET'])]
    #[OA\Tag(name: 'Émotions')]
    #[OA\Get(
        summary: 'Récupérer toutes les émotions actives',
        description: 'Retourne une liste de toutes les émotions actives.',
    )]
    #[OA\Response(
        response: 200,
        description: 'Liste des émotions actives',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                ref: new Model(
                    type: Emotion::class,
                    groups: ['emotion:read']
                )
            )
        )
    )]
    public function getAllActive(EmotionRepository $emotionRepository): Response
    {
        $emotions = $emotionRepository->findBy(['actif' => true]);
        return $this->json($emotions, Response::HTTP_OK, [], ['groups' => 'emotion:read']);
    }

    #[Route('/categorie/{categorieId}', name: 'get_all_by_categorie', methods: ['GET'])]
    #[OA\Tag(name: 'Émotions')]
    #[OA\Get(
        summary: 'Récupérer toutes les émotions par catégorie',
        description: 'Retourne une liste de toutes les émotions actives pour une catégorie spécifique.',
    )]
    #[OA\Parameter(
        name: 'categorieId',
        in: 'path',
        required: true,
        description: 'ID de la catégorie d\'émotions',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Liste des émotions par catégorie',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                ref: new Model(
                    type: Emotion::class,
                    groups: ['emotion:read']
                )
            )
        )
    )]
    
    public function getAllByCategorie(EmotionRepository $emotionRepository, int $categorieId): Response
    {
        $emotions = $emotionRepository->findBy(['categorie' => $categorieId, 'actif' => true]);
        return $this->json($emotions, Response::HTTP_OK, [], ['groups' => 'emotion:read']);
    }

    #[Route('/{id}', name: 'get_one_by_id', methods: ['GET'])]
    #[OA\Tag(name: 'Émotions')]
    #[OA\Get(
        summary: 'Récupérer une émotion par son ID',
        description: 'Retourne une émotion spécifique en fonction de son ID.',
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID de l\'émotion à récupérer',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Émotion trouvée',
        content: new OA\JsonContent(
            ref: new Model(
                type: Emotion::class,
                groups: ['emotion:read']
            )
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Émotion non trouvée',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    description: 'Message d\'erreur'
                )
            ]
        )
    )]
    public function getOneById(EmotionRepository $emotionRepository, int $id): Response
    {
        $emotion = $emotionRepository->find($id);
        
        if (!$emotion) {
            return $this->json(['message' => 'Émotion non trouvée'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($emotion, Response::HTTP_OK, [], ['groups' => 'emotion:read']);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Tag(name: 'Émotions')]
    #[OA\Post(
        summary: 'Créer une nouvelle émotion',
        description: 'Permet de créer une nouvelle émotion avec un nom et une icône.',
    )]
    #[OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'nom',
                            type: 'string',
                            description: 'Nom de l\'émotion'
                        ),
                        new OA\Property(
                            property: 'categorie',
                            type: 'integer',
                            description: 'ID de la catégorie d\'émotion'
                        ),
                        new OA\Property(
                            property: 'icone',
                            type: 'string',
                            format: 'binary',
                            description: 'Image de l\'émotion (PNG)'
                        )
                    ],
                    required: ['nom', 'categorie', 'icone']
                )
            )
        ]
    )]
    #[OA\Response(
        response: 201,
        description: 'Émotion créée avec succès',
        content: new OA\JsonContent(
            ref: new Model(
                type: Emotion::class,
                groups: ['emotion:read']
            )
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Requête invalide, nom et icône requis',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    description: 'Message d\'erreur'
                )
            ]
        )
    )]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        // Extraire les données de la requête (JSON ou multipart)
        $data = $this->extractRequestData($request, ['nom', 'categorie']);
        $nom = $data['nom'] ?? null;
        $categorieId = $data['categorie'] ?? null;

        if (empty($nom) || empty($categorieId)) {
            return $this->json(['message' => 'Le nom et la catégorie de l\'émotion sont requis'], Response::HTTP_BAD_REQUEST);
        }

        $categorie = $em->getRepository(CategorieEmotion::class)->find($categorieId);
        if (!$categorie) {
            return $this->json(['message' => 'La catégorie d\'émotion est invalide'], Response::HTTP_BAD_REQUEST);
        }

        $emotion = new Emotion();
        $emotion->setNom($nom);
        $emotion->setActif(true);
        $emotion->setDateCreation(new \DateTime());
        $emotion->setDernierModificateur($this->getUser());
        $emotion->setCategorie($categorie);

        // Gestion du fichier image
        $imageFile = $request->files->get('icone');
        if ($imageFile && $imageFile->isValid()) {
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/emotions';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $newFilename = uniqid('emotion_') . '.' . $imageFile->guessExtension();
            $imageFile->move($uploadDir, $newFilename);

            $emotion->setIcone('/uploads/emotions/' . $newFilename);
        } else {
            return $this->json(['message' => 'Une image est requise pour créer une émotion'], Response::HTTP_BAD_REQUEST);
        }

        $em->persist($emotion);
        $em->flush();

        return $this->json($emotion, Response::HTTP_CREATED, [], ['groups' => 'emotion:read']);
    }

    #[Route('/{id}', name: 'update', methods: ['POST'])]
    #[OA\Tag(name: 'Émotions')]
    #[OA\Post(
        summary: 'Mettre à jour une émotion',
        description: 'Permet de mettre à jour une émotion existante.',
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID de l\'émotion à mettre à jour',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'nom',
                            type: 'string',
                            description: 'Nom de l\'émotion'
                        ),
                        new OA\Property(
                            property: 'categorie',
                            type: 'integer',
                            description: 'ID de la catégorie d\'émotion'
                        ),
                        new OA\Property(
                            property: 'icone',
                            type: 'string',
                            format: 'binary',
                            description: 'Image de l\'émotion (PNG) - Optionnel pour la mise à jour'
                        ),
                        new OA\Property(
                            property: 'actif',
                            type: 'boolean',
                            description: 'Statut actif de l\'émotion'
                        )
                    ]
                )
            )
        ]
    )]
    #[OA\Response(
        response: 200,
        description: 'Émotion mise à jour avec succès',
        content: new OA\JsonContent(
            ref: new Model(
                type: Emotion::class,
                groups: ['emotion:read']
            )
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Émotion non trouvée',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    description: 'Message d\'erreur'
                )
            ]
        )
    )]
    public function update(Request $request, EntityManagerInterface $em, int $id): Response
    {
        $data = $this->extractRequestData($request, ['nom', 'categorie', 'actif']);
        $emotion = $em->getRepository(Emotion::class)->find($id);

        if (!$emotion) {
            return $this->json(['message' => 'Émotion non trouvée'], Response::HTTP_NOT_FOUND);
        }

        if (isset($data['nom'])) {
            $emotion->setNom($data['nom']);
        }
        
        if (isset($data['actif'])) {
            $emotion->setActif($data['actif']);
        }
        
        if(isset($data['categorie'])) {
            $categorie = $em->getRepository(CategorieEmotion::class)->find($data['categorie']);
            if (!$categorie) {
                return $this->json(['message' => 'La catégorie d\'émotion est invalide'], Response::HTTP_BAD_REQUEST);
            }
            $emotion->setCategorie($categorie);
        }

        // Gestion du fichier image
        $imageFile = $request->files->get('icone');
        if ($imageFile && $imageFile->isValid()) {
            // Supprimer l'ancienne image si elle existe
            if ($emotion->getIcone()) {
                $oldImagePath = $this->getParameter('kernel.project_dir') . '/public' . $emotion->getIcone();
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/emotions';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $newFilename = uniqid('emotion_') . '.' . $imageFile->guessExtension();
            $imageFile->move($uploadDir, $newFilename);

            $emotion->setIcone('/uploads/emotions/' . $newFilename);
        }
        
        $emotion->setDernierModificateur($this->getUser());
        $em->flush();

        return $this->json($emotion, Response::HTTP_OK, [], ['groups' => 'emotion:read']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Tag(name: 'Émotions')]
    #[OA\Delete(
        summary: 'Supprimer une émotion',
        description: 'Permet de supprimer une émotion en la marquant comme inactive.',
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID de l\'émotion à supprimer',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 204,
        description: 'Émotion supprimée avec succès',
    )]
    #[OA\Response(
        response: 404,
        description: 'Émotion non trouvée',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    description: 'Message d\'erreur'
                )
            ]
        )
    )]
    public function delete(EntityManagerInterface $em, int $id): Response
    {
        $emotion = $em->getRepository(Emotion::class)->find($id);
        if (!$emotion) {
            return $this->json(['message' => 'Émotion non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Supprimer l'image associée si elle existe lors de la suppression définitive
        // Note: Ici on ne fait que désactiver, donc on garde l'image
        $emotion->setActif(false);
        $emotion->setDateSuppression(new \DateTime());
        $emotion->setDernierModificateur($this->getUser());
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}

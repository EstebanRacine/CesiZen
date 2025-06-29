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
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'nom',
                    type: 'string',
                    description: 'Nom de l\'émotion'
                ),
                new OA\Property(
                    property: 'icone',
                    type: 'string',
                    description: 'Icône de l\'émotion'
                )
            ],
            required: ['nom', 'icone']
        )
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
        $data = $this->extractRequestData($request, ['nom', 'icone', 'categorie']);

        if (!isset($data['nom']) || !isset($data['icone']) || !isset($data['categorie'])) {
            return $this->json(['message' => 'Le nom, l\'icône et la catégorie de l\'émotion sont requis'], Response::HTTP_BAD_REQUEST);
        }

        $categorie = $em->getRepository(CategorieEmotion::class)->find($data['categorie']);
        if (!$categorie) {
            return $this->json(['message' => 'La catégorie d\'émotion est invalide'], Response::HTTP_BAD_REQUEST);
        }

        $emotion = new Emotion();
        $emotion->setNom($data['nom']);
        $emotion->setIcone($data['icone']);
        $emotion->setActif(true);
        $emotion->setDateCreation(new \DateTime());
        $emotion->setDernierModificateur($this->getUser());
        $emotion->setCategorie($categorie);
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
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'nom',
                    type: 'string',
                    description: 'Nom de l\'émotion'
                ),
                new OA\Property(
                    property: 'icone',
                    type: 'string',
                    description: 'Icône de l\'émotion'
                ),
                new OA\Property(
                    property: 'actif',
                    type: 'boolean',
                    description: 'Statut actif de l\'émotion'
                )
            ]
        )
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
        $data = $this->extractRequestData($request, ['nom', 'icone', 'categorie']);
        $emotion = $em->getRepository(Emotion::class)->find($id);

        if (!$emotion) {
            return $this->json(['message' => 'Émotion non trouvée'], Response::HTTP_NOT_FOUND);
        }

        if (isset($data['nom'])) {
            $emotion->setNom($data['nom']);
        }
        if (isset($data['icone'])) {
            $emotion->setIcone($data['icone']);
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

        $emotion->setActif(false);
        $emotion->setDateSuppression(new \DateTime());
        $emotion->setDernierModificateur($this->getUser());
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}

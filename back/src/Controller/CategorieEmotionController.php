<?php

namespace App\Controller;

use App\Repository\CategorieEmotionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use App\Entity\CategorieEmotion;

#[Route('/api/categorie-emotion', name: 'categorie_emotion_')]
final class CategorieEmotionController extends AbstractController
{
    #[Route('', name: 'find_all', methods: ['GET'])]
    #[OA\Tag(name: 'Catégories d\'émotions')]
    #[OA\Get(
        summary: 'Récupérer toutes les catégories d\'émotions',
        description: 'Retourne une liste de toutes les catégories d\'émotions disponibles.',
    )]
    #[OA\Response(
        response: 200,
        description: 'Liste des catégories d\'émotions',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                ref: new Model(
                    type: CategorieEmotion::class,
                    groups: ['categorie_emotion:read']
                )
            )
        )
    )]
    public function findAll(CategorieEmotionRepository $categorieRepository): Response
    {
        $categories = $categorieRepository->findAll();
        return $this->json($categories, Response::HTTP_OK, [], ['groups' => 'categorie_emotion:read']);
    }
}

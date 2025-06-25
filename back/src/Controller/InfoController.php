<?php

namespace App\Controller;

use App\Entity\Info;
use App\Repository\InfoRepository;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;

#[Route('/api/info', name: 'info_')]
final class InfoController extends AbstractController
{
    #[Route('', name: 'get_all_infos', methods: ['GET'])]
    #[OA\Tag(name: 'Info')]
    #[OA\Get(
        summary: 'Récupérer toutes les infos',
        description: 'Retourne une liste de toutes les infos actives, triées par date de création décroissante.',
    )]
    #[OA\Response(
        response: 200,
        description: 'Liste des infos actives',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                ref: new Model(
                    type: Info::class, 
                    groups: ['info:read']
                )
            )
        )
    )]
    public function getAllInfosActive(InfoRepository $infoRepository): Response
    {
        $infos = $infoRepository->findBy(['actif' => true], ['dateCreation' => 'DESC']);
        return $this->json($infos, Response::HTTP_OK, [], ['groups' => 'info:read']);
    }

    #[Route('/{id}', name: 'get_info_by_id', methods: ['GET'])]
    #[OA\Tag(name: 'Info')]
    #[OA\Get(
        summary: 'Récupérer une info par son ID',
        description: 'Retourne les détails d\'une info spécifique.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID de l\'info', schema: new OA\Schema(type: 'integer'))
        ]
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID de l\'info',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Détails de l\'info',
        content: new OA\JsonContent(
            ref: new Model(
                type: Info::class,
                groups: ['info:read']
            )
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Info non trouvée',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', description: 'Message d\'erreur')
            ]
        )
    )]
    public function getById(int $id, InfoRepository $infoRepository): Response
    {
        $info = $infoRepository->find($id);
        if (!$info) {
            return $this->json(['message' => 'Info non trouvée'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($info, Response::HTTP_OK, [], ['groups' => 'info:read']);
    }

    #[Route('/menu/{id}', name: 'get_by_menu', methods: ['GET'])]
    #[OA\Tag(name: 'Info')]
    #[OA\Get(
        summary: 'Récupérer les infos par menu',
        description: 'Retourne une liste d\'infos actives associées à un menu spécifique.',
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID du menu pour lequel récupérer les infos',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Liste des infos actives pour le menu',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                ref: new Model(
                    type: Info::class,
                    groups: ['info:read']
                )
            )
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Aucune info trouvée pour ce menu',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', description: 'Message d\'erreur')
            ]
        )
    )]
    public function getByMenu(int $id, InfoRepository $infoRepository): Response
    {
        $infos = $infoRepository->findBy(['menu' => $id, 'actif' => true], ['dateCreation' => 'DESC']);
        if (empty($infos)) {
            return $this->json(['message' => 'Aucune info trouvée pour ce menu'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($infos, Response::HTTP_OK, [], ['groups' => 'info:read']);
    }

    #[Route('/', name: 'create_info', methods: ['POST'])]
    #[OA\Tag(name: 'Info')]
    #[OA\Post(
        summary: 'Créer une nouvelle info',
        description: 'Permet de créer une nouvelle info avec un titre, un contenu et éventuellement une image.',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'titre', type: 'string', description: 'Titre de l\'info'),
                    new OA\Property(property: 'contenu', type: 'string', description: 'Contenu de l\'info'),
                    new OA\Property(property: 'image', type: 'string', nullable: true, description: 'URL de l\'image associée'),
                ],
                required: ['titre', 'contenu']
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Info créée avec succès',
        content: new OA\JsonContent(
            ref: new Model(
                type: Info::class,
                groups: ['info:read']
            )
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Requête invalide, titre et contenu requis',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', description: 'Message d\'erreur')
            ]
        )
    )]
    public function createInfo(Request $request, EntityManagerInterface $em, MenuRepository $menuRepository): Response
    {
        $data = json_decode($request->getContent(), true);
        $titre = $data['titre'] ?? null;
        $contenu = $data['contenu'] ?? null;
        $menu = $data['menu'] ?? null;

        if (empty($titre) || empty($contenu) || empty($menu)) {
            return $this->json(['message' => 'Titre, contenu et menu requis'], Response::HTTP_BAD_REQUEST);
        }

        $menu = $menuRepository->find($menu);
        if (!$menu) {
            return $this->json(['message' => 'Menu non trouvé'], Response::HTTP_BAD_REQUEST);
        }

        $info = new Info();
        $info->setTitre($titre);
        $info->setContenu($contenu);
        $info->setMenu($menu);
        $info->setActif(true);
        $info->setDateCreation(new \DateTime());
        $info->setCreateur($this->getUser());

        // Gestion du fichier image
        $imageFile = $request->files->get('image');
        if ($imageFile && $imageFile->isValid()) {
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/infos';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $newFilename = uniqid('info_') . '.' . $imageFile->guessExtension();
            $imageFile->move($uploadDir, $newFilename);

            $info->setImage('/uploads/infos/' . $newFilename);
        }

        $em->persist($info);
        $em->flush();

        return $this->json($info, Response::HTTP_CREATED, [], ['groups' => 'info:read']);
    }

    #[Route('/{id}', name: 'update_info', methods: ['PUT'])]
    #[OA\Tag(name: 'Info')]
    #[OA\Put(
        summary: 'Mettre à jour une info',
        description: 'Permet de mettre à jour une info existante en fournissant son ID et les nouveaux détails.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID de l\'info', schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'titre', type: 'string', description: 'Titre de l\'info'),
                    new OA\Property(property: 'contenu', type: 'string', description: 'Contenu de l\'info'),
                    new OA\Property(property: 'image', type: 'string', nullable: true, description: 'URL de l\'image associée'),
                ],
                required: ['titre', 'contenu']
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Info mise à jour avec succès',
        content: new OA\JsonContent(
            ref: new Model(
                type: Info::class,
                groups: ['info:read']
            )
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Info non trouvée',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', description: 'Message d\'erreur')
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Requête invalide, titre et contenu requis',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', description: 'Message d\'erreur')
            ]
        )
    )]
    public function updateInfo(int $id, Request $request, InfoRepository $infoRepository, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);

        $info = $infoRepository->find($id);
        if (!$info) {
            return $this->json(['message' => 'Info non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $titre = $data['titre'] ?? null;
        $contenu = $data['contenu'] ?? null;
        

        if (empty($titre) || empty($contenu)) {
            return $this->json(['message' => 'Titre et contenu requis'], Response::HTTP_BAD_REQUEST);
        }

        $info->setTitre($titre);
        $info->setContenu($contenu);
        $info->setDateModification(new \DateTime());
        $info->setModificateur($this->getUser());

        // Gestion du fichier image
        $imageFile = $request->files->get('image');
        if ($imageFile && $imageFile->isValid()) {
            // Supprimer l'ancienne image si elle existe
            if ($info->getImage()) {
                unlink($this->getParameter('kernel.project_dir') . '/public' . $info->getImage());
            }
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/infos';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $newFilename = uniqid('info_') . '.' . $imageFile->guessExtension();
            $imageFile->move($uploadDir, $newFilename);

            $info->setImage('/uploads/infos/' . $newFilename);
        }

        $em->flush();

        return $this->json($info, Response::HTTP_OK, [], ['groups' => 'info:read']);
    }

    #[Route('/{id}', name: 'delete_info', methods: ['DELETE'])]
    #[OA\Tag(name: 'Info')]
    #[OA\Delete(
        summary: 'Supprimer une info',
        description: 'Permet de supprimer une info en fournissant son ID.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID de l\'info', schema: new OA\Schema(type: 'integer'))
        ]
    )]
    #[OA\Response(
        response: 204,
        description: 'Info supprimée avec succès'
    )]
    #[OA\Response(
        response: 404,
        description: 'Info non trouvée',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string', description: 'Message d\'erreur')
            ]
        )
    )]
    public function deleteInfo(int $id, InfoRepository $infoRepository, EntityManagerInterface $em): Response
    {
        $info = $infoRepository->find($id);
        if (!$info) {
            return $this->json(['message' => 'Info non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Supprimer l'image associée si elle existe
        if ($info->getImage()) {
            unlink($this->getParameter('kernel.project_dir') . '/public' . $info->getImage());
        }

        $em->remove($info);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}

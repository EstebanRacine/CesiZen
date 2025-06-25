<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use App\Entity\Menu;
use Nelmio\ApiDocBundle\Attribute\Model;

#[Route('/api/menu', name: 'app_menu_')]
final class MenuController extends AbstractController
{
    #[Route('/all', name: 'get_all', methods: ['GET'])]
    #[OA\Tag(name: 'Menus')]
    #[OA\Get(
        summary: 'Récupérer tous les menus',
        description: 'Retourne une liste de tous les menus.',
    )]
    #[OA\Response(
        response: 200,
        description: 'Liste des menus',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                ref: new Model(
                    type: Menu::class,
                    groups: ['menu:read']
                )
            )
        )
    )]
    public function getAll(MenuRepository $menuRepository): Response
    {
        $menus = $menuRepository->findAll();
        return $this->json($menus, Response::HTTP_OK, [], ['groups' => 'menu:read']);
    }

    #[Route('', name: 'get_all_active', methods: ['GET'])]
    #[OA\Tag(name: 'Menus')]
    #[OA\Get(
        summary: 'Récupérer tous les menus actifs',
        description: 'Retourne une liste de tous les menus actifs.',
    )]
    #[OA\Response(
        response: 200,
        description: 'Liste des menus actifs',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                ref: new Model(
                    type: Menu::class,
                    groups: ['menu:read']
                )
            )
        )
    )]
    public function getAllActive(MenuRepository $menuRepository): Response
    {
        $menus = $menuRepository->findBy(['actif' => true]);
        return $this->json($menus, Response::HTTP_OK, [], ['groups' => 'menu:read']);
    }

    #[Route('/{id}', name: 'get_one_by_id', methods: ['GET'])]
    #[OA\Tag(name: 'Menus')]
    #[OA\Get(
        summary: 'Récupérer un menu par son ID',
        description: 'Retourne un menu spécifique en fonction de son ID.',
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID du menu à récupérer',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Détails du menu',
        content: new OA\JsonContent(
            ref: new Model(
                type: Menu::class,
                groups: ['menu:read']
            )
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Menu non trouvé',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: 'Menu non trouvé'
                )
            ]
        )
    )]
    public function getOneById(int $id, MenuRepository $menuRepository): Response
    {
        $menu = $menuRepository->find($id);
        if (!$menu) {
            return $this->json(['message' => 'Menu non trouvé'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($menu, Response::HTTP_OK, [], ['groups' => 'menu:read']);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Tag(name: 'Menus')]
    #[OA\Post(
        summary: 'Créer un nouveau menu',
        description: 'Permet de créer un nouveau menu avec un nom et une icône.',
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'nom',
                    type: 'string',
                    example: 'Nouveau Menu'
                ),
                new OA\Property(
                    property: 'icone',
                    type: 'string',
                    example: 'icon-new-menu'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Menu créé avec succès',
        content: new OA\JsonContent(
            ref: new Model(
                type: Menu::class,
                groups: ['menu:read']
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
                    example: 'Nom and icone sont requis'
                )
            ]
        )
    )]
    public function create(EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data['nom']) || empty($data['icone'])) {
            return $this->json(['message' => 'Nom and icone sont requis'], Response::HTTP_BAD_REQUEST);
        }

        $menu = new Menu();
        $menu->setNom($data['nom'])
             ->setIcone($data['icone'])
             ->setActif(true)
             ->setDateCreation(new \DateTime())
             ->setDernierModificateur($this->getUser());

        $em->persist($menu);
        $em->flush();

        return $this->json($menu, Response::HTTP_CREATED, [], ['groups' => 'menu:read']);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    #[OA\Tag(name: 'Menus')]
    #[OA\Put(
        summary: 'Mettre à jour un menu',
        description: 'Permet de mettre à jour un menu existant en fonction de son ID.',
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID du menu à mettre à jour',
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
                    example: 'Menu Mis à Jour'
                ),
                new OA\Property(
                    property: 'icone',
                    type: 'string',
                    example: 'icon-updated-menu'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Menu mis à jour avec succès',
        content: new OA\JsonContent(
            ref: new Model(  
                type: Menu::class,
                groups: ['menu:read']
            )
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Menu non trouvé',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: 'Menu non trouvé'
                )
            ]
        )
    )]
    public function update(EntityManagerInterface $em, Request $request, int $id): Response
    {
        $data = json_decode($request->getContent(), true);
        $menu = $em->getRepository(Menu::class)->find($id);

        if (!$menu) {
            return $this->json(['message' => 'Menu non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if (isset($data['nom'])) {
            $menu->setNom($data['nom']);
            $menu->setDernierModificateur($this->getUser());
        }
        if (isset($data['icone'])) {
            $menu->setIcone($data['icone']);
            $menu->setDernierModificateur($this->getUser());
        }

        $em->flush();

        return $this->json($menu, Response::HTTP_OK, [], ['groups' => 'menu:read']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Tag(name: 'Menus')]
    #[OA\Delete(
        summary: 'Supprimer un menu',
        description: 'Permet de supprimer un menu en le marquant comme inactif.',
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID du menu à supprimer',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 204,
        description: 'Menu supprimé avec succès'
    )]
    #[OA\Response(
        response: 404,
        description: 'Menu non trouvé',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    example: 'Menu non trouvé'
                )
            ]
        )
    )]
    public function delete(EntityManagerInterface $em, int $id): Response
    {
        $menu = $em->getRepository(Menu::class)->find($id);
        if (!$menu) {
            return $this->json(['message' => 'Menu non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $menu->setActif(false)
             ->setDateSuppression(new \DateTime())
             ->setDernierModificateur($this->getUser());
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

}

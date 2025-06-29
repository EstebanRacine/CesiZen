<?php

namespace App\Controller;

use App\Controller\AbstractApiController;
use App\Repository\EmotionRepository;
use App\Repository\TrackerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use App\Entity\Tracker;
use Nelmio\ApiDocBundle\Attribute\Model;

#[Route('/api/tracker', name: 'tracker_')]
final class TrackerController extends AbstractApiController
{

    #[Route('/me', name: 'get_mine', methods: ['GET'])]
    #[OA\Tag(name: 'Trackers')]
    #[OA\Get(
        summary: 'Récupérer mes trackers',
        description: 'Retourne une liste de tous les trackers associés à l\'utilisateur connecté.',
    )]
    #[OA\Response(
        response: 200,
        description: 'Liste des trackers de l\'utilisateur',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                ref: new Model(
                    type: Tracker::class,
                    groups: ['tracker:read']
                )
            )
        )
    )]
    public function getByMyUser(TrackerRepository $trackerRepository): Response
    {
        $trackers = $trackerRepository->findBy([
            'user' => $this->getUser(),
            'actif' => true
        ]);
        return $this->json($trackers, Response::HTTP_OK, [], ['groups' => 'tracker:read']);
    }

    #[Route('/me/date/{date}', name: 'get_by_my_user_and_date', methods: ['POST'])]
    public function getByMyUserAndDate(TrackerRepository $trackerRepository, string $date): Response
    {
        if (!$date) {
            return $this->json(['message' => 'Date requise'], Response::HTTP_BAD_REQUEST);
        }

        // Vérification du format de la date
        if (preg_match('/^\d{4}-(\d{2})-(\d{2})$/', $date, $matches)) {
            $month = (int)$matches[1];
            $day = (int)$matches[2];
            $year = (int)substr($date, 0, 4);

            if (!checkdate($month, $day, $year)) {
                return $this->json(['message' => 'Format de date invalide, utilisez YYYY-MM-DD'], Response::HTTP_BAD_REQUEST);
            }
        } else {
            return $this->json(['message' => 'Format de date invalide, utilisez YYYY-MM-DD'], Response::HTTP_BAD_REQUEST);
        }

        $dateTime = \DateTime::createFromFormat('Y-m-d', $date);

        $trackers = $trackerRepository->findByUserAndDate($this->getUser(), $dateTime);
        return $this->json($trackers, Response::HTTP_OK, [], ['groups' => 'tracker:read']);
    }


    #[Route('/me/month/{year}/{month}', name: 'get_by_user_and_month_year', methods: ['GET'])]
    public function getByUserAndMonthYear(TrackerRepository $trackerRepository, int $year, int $month): Response
    {
        if (!checkdate($month, 1, $year)) {
            return $this->json(['message' => 'Mois ou année invalide'], Response::HTTP_BAD_REQUEST);
        }

        $trackers = $trackerRepository->findByUserAndMonthYear($this->getUser(), $year, $month);
        return $this->json($trackers, Response::HTTP_OK, [], ['groups' => 'tracker:read']);
    }

    #[Route('/{id}', name: 'get_by_id', methods: ['GET'])]
    #[OA\Tag(name: 'Trackers')]
    #[OA\Get(
        summary: 'Récupérer un tracker par son ID',
        description: 'Retourne les détails d\'un tracker spécifique en fonction de son ID.',
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID du tracker à récupérer',
    )]
    #[OA\Response(
        response: 200,
        description: 'Détails du tracker',
        content: new OA\JsonContent(
            ref: new Model(
                type: Tracker::class,
                groups: ['tracker:read']
            )
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Tracker non trouvé',
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
    public function getById(int $id, TrackerRepository $trackerRepository): Response
    {
        $tracker = $trackerRepository->find($id);
        if (!$tracker) {
            return $this->json(['message' => 'Tracker non trouvé'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($tracker, Response::HTTP_OK, [], ['groups' => 'tracker:read']);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Tag(name: 'Trackers')]
    #[OA\Post(
        summary: 'Créer un nouveau tracker',
        description: 'Permet de créer un nouveau tracker en associant une émotion et une datetime.',
    )]
    #[OA\RequestBody(
        description: 'Données du tracker à créer',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'emotion',
                    type: 'integer',
                    description: 'ID de l\'émotion associée au tracker'
                ),
                new OA\Property(
                    property: 'datetime',
                    type: 'string',
                    format: 'date-time',
                    description: 'Datetime du tracker au format ISO 8601 (YYYY-MM-DDTHH:MM:SS)'
                ),
                new OA\Property(
                    property: 'commentaire',
                    type: 'string',
                    description: 'Commentaire optionnel pour le tracker'
                )
            ],
            required: ['emotion', 'datetime']
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Tracker créé avec succès',
        content: new OA\JsonContent(
            ref: new Model(
                type: Tracker::class,
                groups: ['tracker:read']
            )
        )
    )]
    public function create(EntityManagerInterface $em, Request $request, EmotionRepository $emotionRepository): Response{
        $data = $this->extractRequestData($request, ['emotion', 'datetime', 'commentaire']);

        if (empty($data['emotion']) || empty($data['datetime'])) {
            return $this->json(['message' => 'Emotion et datetime sont requis'], Response::HTTP_BAD_REQUEST);
        }

        $emotion = $emotionRepository->find($data['emotion']);
        if (!$emotion) {
            return $this->json(['message' => 'Emotion non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $tracker = new Tracker();
        $tracker->setDatetime(new \DateTime($data['datetime']));
        $tracker->setCommentaire($data['commentaire'] ?? null);
        $tracker->setActif(true);
        $tracker->setUser($this->getUser());
        $tracker->setEmotion($emotion);
        $em->persist($tracker);
        $em->flush();

        return $this->json($tracker, Response::HTTP_CREATED, [], ['groups' => 'tracker:read']);
    }

    #[Route('/{id}', name: 'update', methods: ['POST'])]
    #[OA\Tag(name: 'Trackers')]
    #[OA\Post(
        summary: 'Mettre à jour un tracker',
        description: 'Permet de mettre à jour un tracker existant en fonction de son ID.',
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID du tracker à mettre à jour',
    )]
    #[OA\RequestBody(
        description: 'Données du tracker à mettre à jour',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'emotion',
                    type: 'integer',
                    description: 'ID de l\'émotion associée au tracker'
                ),
                new OA\Property(
                    property: 'datetime',
                    type: 'string',
                    format: 'date-time',
                    description: 'Datetime du tracker au format ISO 8601 (YYYY-MM-DDTHH:MM:SS)'
                ),
                new OA\Property(
                    property: 'commentaire',
                    type: 'string',
                    description: 'Commentaire optionnel pour le tracker'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Tracker mis à jour avec succès',
        content: new OA\JsonContent(
            ref: new Model(
                type: Tracker::class,
                groups: ['tracker:read']
            )
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Tracker non trouvé',
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
    #[OA\Response(
        response: 400,
        description: 'Requête invalide',
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
    #[OA\Response(
        response: 403,
        description: 'Accès interdit',
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
    public function update(int $id, EntityManagerInterface $em, Request $request, TrackerRepository $trackerRepository, EmotionRepository $emotionRepository): Response
    {
        $tracker = $trackerRepository->find($id);
        if (!$tracker) {
            return $this->json(['message' => 'Tracker non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if (!$tracker->isActif()) {
            return $this->json(['message' => 'Tracker inactif, impossible de le modifier'], Response::HTTP_BAD_REQUEST);
        } 

        if ($tracker->getUser() !== $this->getUser()) {
            return $this->json(['message' => 'Vous ne pouvez pas modifier ce tracker'], Response::HTTP_FORBIDDEN);
        }

        $data = $this->extractRequestData($request, ['emotion', 'datetime', 'commentaire']);

        if (isset($data['emotion'])) {
            $emotion = $emotionRepository->find($data['emotion']);
            if (!$emotion) {
                return $this->json(['message' => 'Emotion non trouvée'], Response::HTTP_NOT_FOUND);
            }
            $tracker->setEmotion($emotion);
        }

        if (isset($data['datetime'])) {
            try {
                $tracker->setDatetime(new \DateTime($data['datetime']));
            } catch (\Exception $e) {
                return $this->json(['message' => 'Format de date invalide'], Response::HTTP_BAD_REQUEST);
            }
        }

        if (isset($data['commentaire'])) {
            $tracker->setCommentaire($data['commentaire']);
        }

        $em->flush();

        return $this->json($tracker, Response::HTTP_OK, [], ['groups' => 'tracker:read']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Tag(name: 'Trackers')]
    #[OA\Delete(
        summary: 'Supprimer un tracker',
        description: 'Permet de supprimer un tracker en fonction de son ID.',
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID du tracker à supprimer',
    )]
    #[OA\Response(
        response: 204,
        description: 'Tracker supprimé avec succès'
    )]
    #[OA\Response(
        response: 404,
        description: 'Tracker non trouvé',
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
    #[OA\Response(
        response: 403,
        description: 'Accès interdit',
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
    public function delete(int $id, EntityManagerInterface $em, TrackerRepository $trackerRepository): Response
    {
        $tracker = $trackerRepository->find($id);
        if (!$tracker) {
            return $this->json(['message' => 'Tracker non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($tracker->getUser() !== $this->getUser()) {
            return $this->json(['message' => 'Vous ne pouvez pas supprimer ce tracker'], Response::HTTP_FORBIDDEN);
        }

        $tracker->setActif(false);
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

}

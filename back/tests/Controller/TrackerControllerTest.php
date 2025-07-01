<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Response;

final class TrackerControllerTest extends WebTestCase
{
    private string $token;
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine')->getManager();

        // Créer ou récupérer l'utilisateur
        $userRepo = $em->getRepository(User::class);
        $user = $userRepo->findOneBy(['username' => 'alice']);

        if (!$user) {
            $user = new User();
            $user->setUsername('alice');
            $user->setRoles(['ROLE_USER']);
            $user->setPassword(
            $container->get(UserPasswordHasherInterface::class)
                ->hashPassword($user, 'password0')
            );
            $user->setActif(true);
            $user->setDateCreation(new \DateTime());
            $em->persist($user);
            $em->flush();
        }

        $this->token = $container
            ->get(\Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface::class)
            ->create($user);
        }

        protected function getAuthHeaders(): array
        {
        return [
            'HTTP_Authorization' => 'Bearer ' . $this->token,
            'CONTENT_TYPE' => 'application/json',
        ];
        }

        public function testGetMyTrackers(): void
        {
            $this->client->request('GET', '/api/tracker/me', [], [], $this->getAuthHeaders());
            $this->assertResponseIsSuccessful();
            $this->assertResponseFormatSame('json');
            $data = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertIsArray($data);
        }

        public function testGetMyTrackersByDate(): void
        {
            $date = (new \DateTime())->format('Y-m-d');
            $this->client->request('POST', '/api/tracker/me/date/' . $date, [], [], $this->getAuthHeaders());
            $this->assertResponseIsSuccessful();
            $this->assertResponseFormatSame('json');
            $data = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertIsArray($data);
        }

        public function testGetMyTrackersByDateInvalidFormat(): void
        {
            $this->client->request('POST', '/api/tracker/me/date/2024-99-99', [], [], $this->getAuthHeaders());
            $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
            $data = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertEquals('Format de date invalide, utilisez YYYY-MM-DD', $data['message']);
        }

        public function testGetTrackerByIdFound(): void
        {
            // tracker_0 existe via fixtures et appartient à alice
            $this->client->request('GET', '/api/tracker/1', [], [], $this->getAuthHeaders());
            $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
            $this->assertResponseFormatSame('json');
            $data = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertArrayHasKey('id', $data);
        }

        public function testGetTrackerByIdNotFound(): void
        {
            $this->client->request('GET', '/api/tracker/99999', [], [], $this->getAuthHeaders());
            $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
            $data = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertEquals('Tracker non trouvé', $data['message']);
        }

        public function testCreateTrackerSuccess(): void
        {
            // emotion_0 existe via fixtures
            $payload = [
                'emotion' => 1,
                'datetime' => (new \DateTime())->format('Y-m-d\TH:i:s'),
                'commentaire' => 'Test création'
            ];
            $this->client->request(
                'POST',
                '/api/tracker',
                [],
                [],
                $this->getAuthHeaders(),
                json_encode($payload)
            );
            $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
            $data = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertEquals('Test création', $data['commentaire']);
            $this->assertEquals(1, $data['emotion']['id']);
        }

        public function testCreateTrackerMissingFields(): void
        {
            $payload = [
                'emotion' => 1
            ];
            $this->client->request(
                'POST',
                '/api/tracker',
                [],
                [],
                $this->getAuthHeaders(),
                json_encode($payload)
            );
            $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
            $data = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertEquals('Emotion et datetime sont requis', $data['message']);
        }

        public function testCreateTrackerInvalidEmotion(): void
        {
            $payload = [
                'emotion' => 99999,
                'datetime' => (new \DateTime())->format('Y-m-d\TH:i:s')
            ];
            $this->client->request(
                'POST',
                '/api/tracker',
                [],
                [],
                $this->getAuthHeaders(),
                json_encode($payload)
            );
            $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
            $data = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertEquals('Emotion non trouvée', $data['message']);
        }

        public function testUpdateTrackerSuccess(): void
        {
            // Créer un tracker pour etre autorisé à le modifier
            $payload = [
                'emotion' => 1,
                'datetime' => (new \DateTime())->format('Y-m-d\TH:i:s'),
                'commentaire' => 'Commentaire initial'
            ];
            $this->client->request(
                'POST',
                '/api/tracker',
                [],
                [],
                $this->getAuthHeaders(),
                json_encode($payload)
            );
            $data = json_decode($this->client->getResponse()->getContent(), true);
            $trackerId = $data['id'];
            $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
            $this->assertArrayHasKey('id', $data);
            $this->assertEquals('Commentaire initial', $data['commentaire']);
            
            // Mettre à jour le tracker
            $updatePayload = [
                'commentaire' => 'Commentaire modifié',
                'emotion' => 1,
                'datetime' => (new \DateTime())->format('Y-m-d\TH:i:s')
            ];
            $this->client->request(
                'POST',
                '/api/tracker/' . $trackerId,
                [],
                [],
                $this->getAuthHeaders(),
                json_encode($updatePayload)
            );
            $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
            $updatedData = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertEquals('Commentaire modifié', $updatedData['commentaire']);
            $this->assertEquals(1, $updatedData['emotion']['id']);
        }

        public function testUpdateTrackerNotFound(): void
        {
            $payload = [
                'commentaire' => 'Impossible'
            ];
            $this->client->request(
                'POST',
                '/api/tracker/99999',
                [],
                [],
                $this->getAuthHeaders(),
                json_encode($payload)
            );
            $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
            $data = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertEquals('Tracker non trouvé', $data['message']);
        }

        public function testUpdateTrackerInvalidEmotion(): void
        {
            // Créer un tracker pour etre autorisé à le modifier
            $payload = [
                'emotion' => 1,
                'datetime' => (new \DateTime())->format('Y-m-d\TH:i:s'),
                'commentaire' => 'Commentaire initial'
            ];
            $this->client->request(
                'POST',
                '/api/tracker',
                [],
                [],
                $this->getAuthHeaders(),
                json_encode($payload)
            );
            $data = json_decode($this->client->getResponse()->getContent(), true);
            $trackerId = $data['id'];
            $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
            $this->assertArrayHasKey('id', $data);
            $this->assertEquals('Commentaire initial', $data['commentaire']);

            $payload = [
                'emotion' => 99999
            ];
            $this->client->request(
                'POST',
                '/api/tracker/' . $trackerId,
                [],
                [],
                $this->getAuthHeaders(),
                json_encode($payload)
            );
            $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
            $data = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertEquals('Emotion non trouvée', $data['message']);
        }

        public function testUpdateTrackerInvalidDate(): void
        {
            // Créer un tracker pour etre autorisé à le modifier
            $payload = [
                'emotion' => 1,
                'datetime' => (new \DateTime())->format('Y-m-d\TH:i:s'),
                'commentaire' => 'Commentaire initial'
            ];
            $this->client->request(
                'POST',
                '/api/tracker',
                [],
                [],
                $this->getAuthHeaders(),
                json_encode($payload)
            );
            $data = json_decode($this->client->getResponse()->getContent(), true);
            $trackerId = $data['id'];
            $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
            $this->assertArrayHasKey('id', $data);
            $this->assertEquals('Commentaire initial', $data['commentaire']);

            $payload = [
                'datetime' => 'invalid-date'
            ];
            $this->client->request(
                'POST',
                '/api/tracker/' . $trackerId,
                [],
                [],
                $this->getAuthHeaders(),
                json_encode($payload)
            );
            $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
            $data = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertEquals('Format de date invalide', $data['message']);
        }

        public function testDeleteTrackerSuccess(): void
        {
            // Créer un tracker à supprimer
            $payload = [
                'emotion' => 1,
                'datetime' => (new \DateTime())->format('Y-m-d\TH:i:s'),
                'commentaire' => 'À supprimer'
            ];
            $this->client->request(
                'POST',
                '/api/tracker',
                [],
                [],
                $this->getAuthHeaders(),
                json_encode($payload)
            );
            $data = json_decode($this->client->getResponse()->getContent(), true);
            $trackerId = $data['id'];

            $this->client->request(
                'DELETE',
                '/api/tracker/' . $trackerId,
                [],
                [],
                $this->getAuthHeaders()
            );
            $this->assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
        }

        public function testDeleteTrackerNotFound(): void
        {
            $this->client->request(
                'DELETE',
                '/api/tracker/99999',
                [],
                [],
                $this->getAuthHeaders()
            );
            $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
            $data = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertEquals('Tracker non trouvé', $data['message']);
        }
    }

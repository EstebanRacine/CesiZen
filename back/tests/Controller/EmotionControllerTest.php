<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
final class EmotionControllerTest extends WebTestCase
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
        $user = $userRepo->findOneBy(['username' => 'admin']);

        if (!$user) {
            $user = new User();
            $user->setUsername('admin');
            $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
            $user->setPassword(
                $container->get(UserPasswordHasherInterface::class)
                        ->hashPassword($user, 'admin')
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

    public function testGetAllEmotions(): void
    {
        $client = $this->client;
        $client->request('GET', '/api/emotion/all', [], [], $this->getAuthHeaders());
        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    public function testGetAllActiveEmotions(): void
    {
        $client = $this->client;
        $client->request('GET', '/api/emotion', [], [], $this->getAuthHeaders());
        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        foreach ($data as $emotion) {
            $this->assertTrue($emotion['actif']);
        }
    }

    public function testGetAllByCategorie(): void
    {
        $client = $this->client;
        // Catégorie 0 existe via fixtures
        $client->request('GET', '/api/emotion/categorie/0', [], [], $this->getAuthHeaders());
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertResponseFormatSame('json');
    }

    public function testGetOneByIdFound(): void
    {
        $client = $this->client;
        // Emotion 1 existe via fixtures
        $client->request('GET', '/api/emotion/1', [], [], $this->getAuthHeaders());
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertResponseFormatSame('json');
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
    }

    public function testGetOneByIdNotFound(): void
    {
        $client = $this->client;
        $client->request('GET', '/api/emotion/99999', [], [], $this->getAuthHeaders());
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Émotion non trouvée', $data['message']);
    }

    public function testCreateEmotionMissingFields(): void
    {
        $client = $this->client;
        $payload = [
            'nom' => 'Incomplete'
            // missing icone and categorie
        ];
        $client->request('POST', '/api/emotion', [], [], $this->getAuthHeaders(), json_encode($payload));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertStringContainsString('requis', $data['message']);
    }

    public function testCreateEmotionInvalidCategorie(): void
    {
        $client = $this->client;
        $payload = [
            'nom' => 'InvalidCat',
            'icone' => 'icon',
            'categorie' => 99999
        ];
        $client->request('POST', '/api/emotion', [], [], $this->getAuthHeaders(), json_encode($payload));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertStringContainsString('catégorie', $data['message']);
    }

    public function testUpdateEmotionSuccess(): void
    {
        $client = $this->client;
        $payload = [
            'nom' => 'UpdatedName',
            'icone' => 'updatedicon',
            'actif' => false
        ];
        $client->request('POST', '/api/emotion/1', [], [], $this->getAuthHeaders(), json_encode($payload));
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('UpdatedName', $data['nom']);
        $this->assertFalse($data['actif']);
    }

    public function testUpdateEmotionNotFound(): void
    {
        $client = $this->client;
        $payload = [
            'nom' => 'ShouldNotExist'
        ];
        $client->request('POST', '/api/emotion/99999', [], [], $this->getAuthHeaders(), json_encode($payload));
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Émotion non trouvée', $data['message']);
    }

    public function testUpdateEmotionInvalidCategorie(): void
    {
        $client = $this->client;
        $payload = [
            'categorie' => 99999
        ];
        $client->request('POST', '/api/emotion/1', [], [], $this->getAuthHeaders(), json_encode($payload));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertStringContainsString('catégorie', $data['message']);
    }

    public function testDeleteEmotionNotFound(): void
    {
        $client = $this->client;
        $client->request('DELETE', '/api/emotion/99999', [], [], $this->getAuthHeaders());
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Émotion non trouvée', $data['message']);
    }
}

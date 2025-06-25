<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Entity\Info;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class InfoControllerTest extends WebTestCase
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
        $user = $userRepo->findOneBy(['username' => 'alice0']);

        if (!$user) {
            $user = new User();
            $user->setUsername('alice0');
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

    public function testGetAllInfosActive(): void
    {
        $this->client->request('GET', '/api/info', [], [], $this->getAuthHeaders());
        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        foreach ($data as $info) {
            $this->assertTrue($info['actif']);
        }
    }

    public function testGetInfoByIdFound(): void
    {
        // On suppose que l'info 1 existe via fixtures
        $this->client->request('GET', '/api/info/1', [], [], $this->getAuthHeaders());
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertResponseFormatSame('json');
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals(1, $data['id']);
    }

    public function testGetInfoByIdNotFound(): void
    {
        $this->client->request('GET', '/api/info/99999', [], [], $this->getAuthHeaders());
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Info non trouvée', $data['message']);
    }

    public function testGetByMenuFound(): void
    {
        $this->client->request('GET', '/api/info/menu/1', [], [], $this->getAuthHeaders());
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertResponseFormatSame('json');
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        foreach ($data as $info) {
            $this->assertTrue($info['actif']);
            $this->assertEquals(1, $info['menu']['id']);
        }
    }

    public function testGetByMenuNotFound(): void
    {
        $this->client->request('GET', '/api/info/menu/99999', [], [], $this->getAuthHeaders());
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Aucune info trouvée pour ce menu', $data['message']);
    }

    public function testCreateInfoSuccess(): void
    {
        $payload = json_encode([
            'titre' => 'Test Info',
            'contenu' => 'Contenu de test',
            'menu' => 1
        ]);

        $this->client->request(
            'POST',
            '/api/info/',
            [],
            [],
            $this->getAuthHeaders(),
            $payload
        );
        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Test Info', $data['titre']);
        $this->assertEquals('Contenu de test', $data['contenu']);
        $this->assertTrue($data['actif']);
    }

    public function testCreateInfoMissingFields(): void
    {
        $payload = json_encode([
            'titre' => 'Incomplete'
            // missing contenu
        ]);
        $this->client->request(
            'POST',
            '/api/info/',
            [],
            [],
            $this->getAuthHeaders(),
            $payload
        );
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertStringContainsString('requis', $data['message']);
    }

    public function testUpdateInfoSuccess(): void
    {
        // On suppose que l'info 1 existe via fixtures
        $payload = json_encode([
            'titre' => 'Titre modifié',
            'contenu' => 'Contenu modifié'
        ]);
        $this->client->request(
            'PUT',
            '/api/info/1',
            [],
            [],
            $this->getAuthHeaders(),
            $payload
        );
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Titre modifié', $data['titre']);
        $this->assertEquals('Contenu modifié', $data['contenu']);
    }

    public function testUpdateInfoNotFound(): void
    {
        $payload = json_encode([
            'titre' => 'Titre',
            'contenu' => 'Contenu'
        ]);
        $this->client->request(
            'PUT',
            '/api/info/99999',
            [],
            [],
            $this->getAuthHeaders(),
            $payload
        );
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Info non trouvée', $data['message']);
    }

    public function testUpdateInfoMissingFields(): void
    {
        // On suppose que l'info 1 existe via fixtures
        $payload = json_encode([
            'titre' => 'Titre seulement'
            // missing contenu
        ]);
        $this->client->request(
            'PUT',
            '/api/info/1',
            [],
            [],
            $this->getAuthHeaders(),
            $payload
        );
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertStringContainsString('requis', $data['message']);
    }

    public function testDeleteInfoSuccess(): void
    {
        // Créer une info à supprimer
        $payload = json_encode([
            'titre' => 'ToDelete',
            'contenu' => 'À supprimer',
            'menu' => 1
        ]);
        $this->client->request(
            'POST',
            '/api/info/',
            [],
            [],
            $this->getAuthHeaders(),
            $payload
        );
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $id = $data['id'];

        $this->client->request('DELETE', '/api/info/' . $id, [], [], $this->getAuthHeaders());
        $this->assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteInfoNotFound(): void
    {
        $this->client->request('DELETE', '/api/info/99999', [], [], $this->getAuthHeaders());
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Info non trouvée', $data['message']);
    }
}

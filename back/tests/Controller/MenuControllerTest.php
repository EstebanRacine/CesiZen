<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\Entity\Menu;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Response;

final class MenuControllerTest extends WebTestCase
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

    public function testGetAllMenus(): void
    {
        $this->client->request('GET', '/api/menu/all', [], [], $this->getAuthHeaders());
        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
    }

    public function testGetAllActiveMenus(): void
    {
        $this->client->request('GET', '/api/menu', [], [], $this->getAuthHeaders());
        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        foreach ($data as $menu) {
            $this->assertArrayHasKey('actif', $menu);
            $this->assertTrue($menu['actif']);
        }
    }

    public function testGetOneByIdFound(): void
    {
        $this->client->request('GET', '/api/menu/1', [], [], $this->getAuthHeaders());
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertResponseFormatSame('json');
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals(1, $data['id']);
    }

    public function testGetOneByIdNotFound(): void
    {
        $this->client->request('GET', '/api/menu/99999', [], [], $this->getAuthHeaders());
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Menu non trouvé', $data['message']);
    }

    public function testCreateMenuSuccess(): void
    {
        $payload = [
            'nom' => 'TestMenu',
            'icone' => 'testicon'
        ];
        $this->client->request(
            'POST',
            '/api/menu',
            [],
            [],
            $this->getAuthHeaders(),
            json_encode($payload)
        );
        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('TestMenu', $data['nom']);
        $this->assertEquals('testicon', $data['icone']);
        $this->assertTrue($data['actif']);
    }

    public function testCreateMenuMissingFields(): void
    {
        $payload = [
            'nom' => ''
        ];
        $this->client->request(
            'POST',
            '/api/menu',
            [],
            [],
            $this->getAuthHeaders(),
            json_encode($payload)
        );
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertStringContainsString('Nom and icone sont requis', $data['message']);
    }

    public function testUpdateMenuSuccess(): void
    {
        $payload = [
            'nom' => 'MenuUpdated',
            'icone' => 'updatedicon'
        ];
        $this->client->request(
            'PUT',
            '/api/menu/1',
            [],
            [],
            $this->getAuthHeaders(),
            json_encode($payload)
        );
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('MenuUpdated', $data['nom']);
        $this->assertEquals('updatedicon', $data['icone']);
    }

    public function testUpdateMenuNotFound(): void
    {
        $payload = [
            'nom' => 'MenuUpdated',
            'icone' => 'updatedicon'
        ];
        $this->client->request(
            'PUT',
            '/api/menu/99999',
            [],
            [],
            $this->getAuthHeaders(),
            json_encode($payload)
        );
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Menu non trouvé', $data['message']);
    }

    public function testDeleteMenuSuccess(): void
    {
        // Créer un menu à supprimer
        $payload = [
            'nom' => 'MenuToDelete',
            'icone' => 'deleteicon'
        ];
        $this->client->request(
            'POST',
            '/api/menu',
            [],
            [],
            $this->getAuthHeaders(),
            json_encode($payload)
        );
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $menuId = $data['id'];

        $this->client->request(
            'DELETE',
            '/api/menu/' . $menuId,
            [],
            [],
            $this->getAuthHeaders()
        );
        $this->assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteMenuNotFound(): void
    {
        $this->client->request(
            'DELETE',
            '/api/menu/99999',
            [],
            [],
            $this->getAuthHeaders()
        );
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Menu non trouvé', $data['message']);
    }
}

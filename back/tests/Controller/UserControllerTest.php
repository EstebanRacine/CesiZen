<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserControllerTest extends WebTestCase
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

    public function testGetByIdNotFound(): void
    {
        $client = $this->client;
        $client->request('GET', '/api/user/999999', [], [], $this->getAuthHeaders());
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertJson($client->getResponse()->getContent());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Utilisateur non trouvé', $data['message']);
    }

    public function testCreateUserBadRequest(): void
    {
        $client = $this->client;
        $client->request('POST', '/api/user/', [], [], $this->getAuthHeaders(), '');
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Username et mot de passe requis', $data['message']);
    }

    public function testCreateUserPasswordTooWeak(): void
    {
        $client = $this->client;
        $payload = [
            'username' => 'testuser987645',
            'password' => 'weak'
        ];
        $client->request('POST', '/api/user/', [], [], $this->getAuthHeaders(), json_encode($payload));
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertStringContainsString('mot de passe doit contenir', $data['message']);
    }

    public function testCreateUserSuccess(): void
    {
        $client = $this->client;
        $username = 'user_' . uniqid();
        $payload = [
            'username' => $username,
            'password' => 'Password1*'
        ];
        $client->request('POST', '/api/user/', [], [], $this->getAuthHeaders(), json_encode($payload));
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals($username, $data['username']);
        $this->assertArrayHasKey('token', $data);
    }

    public function testCreateUserConflict(): void
    {
        $client = $this->client;
        $username = 'user_' . uniqid();
        $payload = [
            'username' => $username,
            'password' => 'Password1*'
        ];
        // First creation
        $client->request('POST', '/api/user/', [], [], $this->getAuthHeaders(), json_encode($payload));
        // Second creation with same username
        $client->request('POST', '/api/user/', [], [], $this->getAuthHeaders(), json_encode($payload));
        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Nom d\'utilisateur déjà pris', $data['message']);
    }

    public function testChangeUsernameBadRequest(): void
    {
        $client = $this->client;
        $client->request('POST', '/api/user/change-username', [], [], $this->getAuthHeaders(), json_encode([]));
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('ID de l\'utilisateur et nouveau nom d\'utilisateur requis', $data['message']);
    }

    public function testChangeUsernameNotFound(): void
    {
        $client = $this->client;
        $payload = [
            'id' => 999999,
            'username' => 'newusername'
        ];
        $client->request('POST', '/api/user/change-username', [], [], $this->getAuthHeaders(), json_encode($payload));
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Utilisateur non trouvé', $data['message']);
    }

    public function testChangeUsernameConflict(): void
    {
        $client = $this->client;
        // Create two users
        $username1 = 'user_' . uniqid();
        $username2 = 'user_' . uniqid();
        $client->request('POST', '/api/user/', [], [], $this->getAuthHeaders(), json_encode([
            'username' => $username1,
            'password' => 'Password1*'
        ]));
        $id1 = json_decode($client->getResponse()->getContent(), true)['id'];
        $client->request('POST', '/api/user/', [], [], $this->getAuthHeaders(), json_encode([
            'username' => $username2,
            'password' => 'Password1*'
        ]));
        // Try to change user1's username to user2's username
        $client->request('POST', '/api/user/change-username', [], [], $this->getAuthHeaders(), json_encode([
            'id' => $id1,
            'username' => $username2
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Nom d\'utilisateur déjà pris', $data['message']);
    }

    public function testAdminResetPasswordBadRequest(): void
    {
        $client = $this->client;
        $client->request('POST', '/api/user/admin/reset-password', [], [], $this->getAuthHeaders(), json_encode([]));
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('ID de l\'utilisateur et nouveau mot de passe requis', $data['message']);
    }

    public function testAdminResetPasswordNotFound(): void
    {
        $client = $this->client;
        $client->request('POST', '/api/user/admin/reset-password', [], [], $this->getAuthHeaders(), json_encode([
            'id' => 999999,
            'new_password' => 'Password1*'
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Utilisateur non trouvé', $data['message']);
    }

    public function testAdminResetPasswordWeakPassword(): void
    {
        $client = $this->client;
        // Create a user
        $username = 'user_' . uniqid();
        $client->request('POST', '/api/user/', [], [], $this->getAuthHeaders(), json_encode([
            'username' => $username,
            'password' => 'Password1*'
        ]));
        $id = json_decode($client->getResponse()->getContent(), true)['id'];
        // Try to reset password with weak password
        $client->request('POST', '/api/user/admin/reset-password', [], [], $this->getAuthHeaders(), json_encode([
            'id' => $id,
            'new_password' => 'weak'
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertStringContainsString('mot de passe doit contenir', $data['message']);
    }
}

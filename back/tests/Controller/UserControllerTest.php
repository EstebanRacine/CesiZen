<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class UserControllerTest extends WebTestCase
{
    public function testGetByIdNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/user/999999');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertJson($client->getResponse()->getContent());
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Utilisateur non trouvé', $data['message']);
    }

    public function testCreateUserBadRequest(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/user/', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([]));
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Username et mot de passe requis', $data['message']);
    }

    public function testCreateUserPasswordTooWeak(): void
    {
        $client = static::createClient();
        $payload = [
            'username' => 'testuser',
            'password' => 'weak'
        ];
        $client->request('POST', '/api/user/', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertStringContainsString('mot de passe doit contenir', $data['message']);
    }

    public function testCreateUserSuccess(): void
    {
        $client = static::createClient();
        $username = 'user_' . uniqid();
        $payload = [
            'username' => $username,
            'password' => 'Password1'
        ];
        $client->request('POST', '/api/user/', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertEquals($username, $data['username']);
        $this->assertArrayHasKey('token', $data);
    }

    public function testCreateUserConflict(): void
    {
        $client = static::createClient();
        $username = 'user_' . uniqid();
        $payload = [
            'username' => $username,
            'password' => 'Password1'
        ];
        // First creation
        $client->request('POST', '/api/user/', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));
        // Second creation with same username
        $client->request('POST', '/api/user/', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));
        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Nom d\'utilisateur déjà pris', $data['message']);
    }

    public function testChangeUsernameBadRequest(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/user/change-username', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([]));
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('ID de l\'utilisateur et nouveau nom d\'utilisateur requis', $data['message']);
    }

    public function testChangeUsernameNotFound(): void
    {
        $client = static::createClient();
        $payload = [
            'id' => 999999,
            'username' => 'newusername'
        ];
        $client->request('POST', '/api/user/change-username', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Utilisateur non trouvé', $data['message']);
    }

    public function testChangeUsernameConflict(): void
    {
        $client = static::createClient();
        // Create two users
        $username1 = 'user_' . uniqid();
        $username2 = 'user_' . uniqid();
        $client->request('POST', '/api/user/', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'username' => $username1,
            'password' => 'Password1'
        ]));
        $id1 = json_decode($client->getResponse()->getContent(), true)['id'];
        $client->request('POST', '/api/user/', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'username' => $username2,
            'password' => 'Password1'
        ]));
        // Try to change user1's username to user2's username
        $client->request('POST', '/api/user/change-username', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'id' => $id1,
            'username' => $username2
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Nom d\'utilisateur déjà pris', $data['message']);
    }

    public function testResetPasswordUnauthorized(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/user/reset-password', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'old_password' => 'Password1',
            'new_password' => 'Password2'
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Non autorisé', $data['message']);
    }

    public function testAdminResetPasswordBadRequest(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/user/admin/reset-password', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([]));
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('ID de l\'utilisateur et nouveau mot de passe requis', $data['message']);
    }

    public function testAdminResetPasswordNotFound(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/user/admin/reset-password', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'id' => 999999,
            'new_password' => 'Password1'
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Utilisateur non trouvé', $data['message']);
    }

    public function testAdminResetPasswordWeakPassword(): void
    {
        $client = static::createClient();
        // Create a user
        $username = 'user_' . uniqid();
        $client->request('POST', '/api/user/', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'username' => $username,
            'password' => 'Password1'
        ]));
        $id = json_decode($client->getResponse()->getContent(), true)['id'];
        // Try to reset password with weak password
        $client->request('POST', '/api/user/admin/reset-password', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'id' => $id,
            'new_password' => 'weak'
        ]));
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertStringContainsString('mot de passe doit contenir', $data['message']);
    }
}

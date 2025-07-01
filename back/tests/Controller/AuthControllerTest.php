<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AuthControllerTest extends WebTestCase
{
    private $client;
    private ?EntityManagerInterface $entityManager = null;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();
        $this->passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
    }

    // ============= TESTS POUR /api/login =============

    public function testLoginSuccess(): void
    {
        
        // Supprime l'uilisateur de test créé
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'testuser1']);
        if ($user) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
        }

        $this->client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'username' => 'testuser1',
            'password' => 'password123!'
        ]));

        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'username' => 'testuser1',
            'password' => 'password123!'
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Connexion réussie', $responseData['message']);
        $this->assertArrayHasKey('token', $responseData);
    }

    public function testLoginBadRequest(): void
    {
        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'username' => 'testuser2'
            // password manquant
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Identifiants manquants', $responseData['message']);
    }

    public function testLoginUnauthorized(): void
    {
        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'username' => 'nonexistentuser3',
            'password' => 'password123!'
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Utilisateur non trouvé', $responseData['message']);
    }

    public function testLoginForbidden(): void
    {
        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'username' => 'inactiveuser4',
            'password' => 'password123!'
        ]));

        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'username' => 'inactiveuser4',
            'password' => 'password123!'
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Compte utilisateur désactivé', $responseData['message']);
    }

    // ============= TESTS POUR /api/register =============

    public function testRegisterSuccess(): void
    {
        // Supprime l'uilisateur de test créé
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => 'newuser54789']);
        if ($user) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
        }

        $this->client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'username' => 'newuser54789',
            'password' => 'password123!'
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Utilisateur créé avec succès', $responseData['message']);
        $this->assertArrayHasKey('token', $responseData);

    }

    public function testRegisterBadRequest(): void
    {
        $this->client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'username' => 'ab', // Trop court
            'password' => '123' // Trop court
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Erreur de validation', $responseData['message']);
        $this->assertArrayHasKey('errors', $responseData);
    }

    public function testRegisterConflict(): void
    {

        $this->client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'username' => 'existinguser689855',
            'password' => 'password123!'
        ]));

        $this->client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'username' => 'existinguser689855',
            'password' => 'password123!'
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Ce nom d\'utilisateur est déjà pris', $responseData['message']);
    }

    // ============= MÉTHODES UTILITAIRES =============

    private function createTestUser(string $username, string $password, bool $actif): User
    {
        $user = new User();
        $user->setUsername($username);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setRoles(['ROLE_USER']);
        $user->setActif($actif);
        $user->setDateCreation(new \DateTime());

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}

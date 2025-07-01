<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class CategorieEmotionControllerTest extends WebTestCase
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

    public function testFindAllReturnsCategories(): void
    {
        $client = $this->client;
        $client->request('GET', '/api/categorie-emotion', [], [], $this->getAuthHeaders());

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
    }

}

<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class CategorieEmotionControllerTest extends WebTestCase
{
    public function testFindAllReturnsCategories(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/categorie-emotion');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
    }

}

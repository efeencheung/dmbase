<?php

namespace Dm\Bundle\ThemeBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StatusPageControllerTest extends WebTestCase
{
    public function test403()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/403');
    }

    public function test404()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/404');
    }

}

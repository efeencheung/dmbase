<?php

namespace Dm\Bundle\AttachmentBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PictureControllerTest extends WebTestCase
{
    public function testAjaxupdate()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/ajaxUpdate');
    }

}

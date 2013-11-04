<?php
namespace SRIO\RestUploadBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UploadControllerTest extends WebTestCase
{
    public function testUpload()
    {
        $client = static::createClient();
        $client->request('POST', '/upload');
        $response = $client->getResponse();

        var_dump($response->getStatusCode(), $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
<?php
namespace SRIO\RestUploadBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UploadControllerTest extends WebTestCase
{
    public function testSimpleUpload()
    {
        $client = static::createClient();
        $client->request('POST', '/upload', array(
            'uploadType' => 'simple'
        ));

        $response = $client->getResponse();

    }
}
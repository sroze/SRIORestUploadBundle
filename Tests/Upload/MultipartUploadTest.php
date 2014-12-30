<?php
namespace SRIO\RestUploadBundle\Tests\Upload;

class MultipartUploadTest extends AbstractUploadTestCase
{
    public function testWithoutContent()
    {
        $client = static::createClient();
        $queryParameters = array('name' => 'test');

        $boundary = uniqid();
        $content = '--'.$boundary.PHP_EOL.'Content-Type: application/json; charset=UTF-8'.PHP_EOL.PHP_EOL.json_encode($queryParameters).PHP_EOL.PHP_EOL;
        $content .= '--'.$boundary.'--';

        $client->request('POST', '/upload?uploadType=multipart', array(), array(), array(
            'CONTENT_TYPE' => 'multipart/related; boundary="'.$boundary.'"',
            'CONTENT_LENGTH' => strlen($content)
        ), $content);
        $this->assertResponseHasErrors($client);
    }

    public function testWithoutHeaders()
    {
        $client = static::createClient();
        $queryParameters = array('name' => 'test');

        $boundary = uniqid();
        $image = $this->getResource($client, 'apple.gif');
        $content = '--'.$boundary.PHP_EOL.'Content-Type: image/gif'.PHP_EOL.PHP_EOL.$image.PHP_EOL.PHP_EOL;
        $content .= '--'.$boundary.PHP_EOL.'Content-Type: application/json; charset=UTF-8'.PHP_EOL.PHP_EOL.json_encode($queryParameters).PHP_EOL.PHP_EOL;
        $content .= '--'.$boundary.'--';

        $client->request('POST', '/upload?uploadType=multipart', array(), array(), array(), $content);
        $this->assertResponseHasErrors($client);
    }

    public function testWithoutBoundary()
    {
        $client = static::createClient();
        $queryParameters = array('name' => 'test');

        $boundary = uniqid();
        $image = $this->getResource($client, 'apple.gif');
        $content = '--'.$boundary.PHP_EOL.'Content-Type: image/gif'.PHP_EOL.PHP_EOL.$image.PHP_EOL.PHP_EOL;
        $content .= '--'.$boundary.PHP_EOL.'Content-Type: application/json; charset=UTF-8'.PHP_EOL.PHP_EOL.json_encode($queryParameters).PHP_EOL.PHP_EOL;
        $content .= '--'.$boundary.'--';

        $client->request('POST', '/upload?uploadType=multipart', array(), array(), array(
            'CONTENT_TYPE' => 'multipart/related',
            'CONTENT_LENGTH' => strlen($content)
        ), $content);
        $this->assertResponseHasErrors($client);
    }

    public function testBinaryBeforeMeta()
    {
        $client = static::createClient();
        $queryParameters = array('name' => 'test');

        $boundary = uniqid();
        $image = $this->getResource($client, 'apple.gif');
        $content = '--'.$boundary.PHP_EOL.'Content-Type: image/gif'.PHP_EOL.PHP_EOL.$image.PHP_EOL.PHP_EOL;
        $content .= '--'.$boundary.PHP_EOL.'Content-Type: application/json; charset=UTF-8'.PHP_EOL.PHP_EOL.json_encode($queryParameters).PHP_EOL.PHP_EOL;
        $content .= '--'.$boundary.'--';

        $client->request('POST', '/upload?uploadType=multipart', array(), array(), array(
            'CONTENT_TYPE' => 'multipart/related; boundary="'.$boundary.'"',
            'CONTENT_LENGTH' => strlen($content)
        ), $content);
        $this->assertResponseHasErrors($client);
    }

    public function testMultipartUpload()
    {
        $client = static::createClient();
        $queryParameters = array('name' => 'test');

        $boundary = uniqid();
        $image = $this->getResource($client, 'apple.gif');
        $content = '--'.$boundary.PHP_EOL.'Content-Type: application/json; charset=UTF-8'.PHP_EOL.PHP_EOL.json_encode($queryParameters).PHP_EOL.PHP_EOL;
        $content .= '--'.$boundary.PHP_EOL.'Content-Type: image/gif'.PHP_EOL.PHP_EOL.$image.PHP_EOL.PHP_EOL;
        $content .= '--'.$boundary.'--';

        $client->request('POST', '/upload?uploadType=multipart', array(), array(), array(
            'CONTENT_TYPE' => 'multipart/related; boundary="'.$boundary.'"',
            'CONTENT_LENGTH' => strlen($content)
        ), $content);

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $jsonContent = json_decode($response->getContent(), true);
        $this->assertNotEmpty($jsonContent);
        $this->assertFalse(array_key_exists('errors', $jsonContent));
        $this->assertTrue(array_key_exists('path', $jsonContent));
        $this->assertTrue(array_key_exists('size', $jsonContent));
        $this->assertTrue(array_key_exists('name', $jsonContent));
        $this->assertEquals('test', $jsonContent['name']);
        $this->assertEquals(strlen($image), $jsonContent['size']);

        $filePath = $this->getUploadedFilePath($client).$jsonContent['path'];
        $this->assertTrue(file_exists($filePath));
        $this->assertEquals($image, file_get_contents($filePath));
        $this->assertTrue(array_key_exists('id', $jsonContent));
        $this->assertNotEmpty($jsonContent['id']);
    }
}

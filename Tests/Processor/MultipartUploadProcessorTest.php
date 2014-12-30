<?php
namespace SRIO\RestUploadBundle\Tests\Processor;

use SRIO\RestUploadBundle\Processor\MultipartUploadProcessor;
use Symfony\Component\HttpFoundation\HeaderBag;

class MultipartUploadProcessorTest extends AbstractProcessorTestCase
{
    public function testGetPartsString()
    {
        $client = static::createClient();
        $image = $this->getResource($client, 'apple.gif');
        $data = array('test' => 'OK');
        $jsonData = json_encode($data);

        $multipartUploadProcessor = $this->getProcessor();
        $request = $this->createMultipartRequest($jsonData, $image);

        $partOne = $this->callMethod($multipartUploadProcessor, 'getPart', array($request, 1));
        $this->assertTrue(is_array($partOne));
        list($contentType, $body) = $partOne;

        $this->assertEquals('application/json; charset=UTF-8', $contentType);
        $this->assertEquals($jsonData, $body);

        $partTwo = $this->callMethod($multipartUploadProcessor, 'getPart', array($request, 2));
        $this->assertTrue(is_array($partTwo));
        list($contentType, $body) = $partTwo;

        $this->assertEquals('image/gif', $contentType);
        $this->assertEquals($image, $body);
    }

    public function testGetPartsResource()
    {
        $client = static::createClient();
        $image = $this->getResource($client, 'apple.gif');
        $data = array('test' => 'OK');
        $jsonData = json_encode($data);
        $boundary = uniqid();
        $content = $this->createMultipartContent($boundary, $jsonData, $image);

        $tempFile = $this->getResourcePath($client, 'test.tmp');
        file_put_contents($tempFile, $content);
        $resource = fopen($tempFile, 'r');

        $multipartUploadProcessor = $this->getProcessor();
        $request = $this->createMultipartRequestWithContent($boundary, $resource);

        $partOne = $this->callMethod($multipartUploadProcessor, 'getPart', array($request, 1));
        $this->assertTrue(is_array($partOne));
        list($contentType, $body) = $partOne;

        $this->assertEquals('application/json; charset=UTF-8', $contentType);
        $this->assertEquals($jsonData, $body);

        $partTwo = $this->callMethod($multipartUploadProcessor, 'getPart', array($request, 2));
        $this->assertTrue(is_array($partTwo));
        list($contentType, $body) = $partTwo;

        $this->assertEquals('image/gif', $contentType);
        $this->assertEquals($image, $body);

        // Clean up
        fclose($resource);
        unlink($tempFile);
    }

    protected function createMultipartRequest($jsonData, $binaryContent)
    {
        $boundary = uniqid();
        $content = $this->createMultipartContent($boundary, $jsonData, $binaryContent);

        return $this->createMultipartRequestWithContent($boundary, $content);
    }

    protected function createMultipartContent($boundary, $jsonData, $binaryContent)
    {
        $content = '--'.$boundary.PHP_EOL.'Content-Type: application/json; charset=UTF-8'.PHP_EOL.PHP_EOL.$jsonData.PHP_EOL.PHP_EOL;
        $content .= '--'.$boundary.PHP_EOL.'Content-Type: image/gif'.PHP_EOL.PHP_EOL.$binaryContent.PHP_EOL.PHP_EOL;
        $content .= '--'.$boundary.'--';

        return $content;
    }

    protected function createMultipartRequestWithContent($boundary, $content)
    {
        $request = $this->getMock('\Symfony\Component\HttpFoundation\Request');
        $request->expects($this->any())
            ->method('getContent')
            ->will($this->returnValue($content));

        $request->headers = new HeaderBag(array(
            'Content-Type' => 'multipart/related; boundary="'.$boundary.'"'
        ));

        return $request;
    }

    protected function getProcessor()
    {
        $voter = $this->getMock(
            'SRIO\RestUploadBundle\Voter\StorageVoter'
        );

        $storageHandler = $this->getMock(
            '\SRIO\RestUploadBundle\Upload\StorageHandler',
            array(),
            array($voter)
        );

        $processor = $this->getMock(
            '\SRIO\RestUploadBundle\Processor\MultipartUploadProcessor',
            array(),
            array($storageHandler)
        );

        return $processor;
    }
}

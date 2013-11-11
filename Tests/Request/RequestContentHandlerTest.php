<?php
namespace SRIO\RestUploadBundle\Tests\Upload\Processor;

use SRIO\RestUploadBundle\Exception\UploadProcessorException;
use SRIO\RestUploadBundle\Tests\Upload\AbstractUploadTestCase;
use SRIO\RestUploadBundle\Upload\Processor\ResumableUploadProcessor;
use SRIO\RestUploadBundle\Upload\Request\RequestContentHandler;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RequestContentHandlerTest extends AbstractUploadTestCase
{
    public function testBinaryStringContent ()
    {
        $client = static::createClient();
        $filePath = $this->getResourcePath($client, 'apple.gif');
        $content = file_get_contents($filePath);

        $this->doTest($content, $content);
    }

    public function testBinaryResourceContent ()
    {
        $client = static::createClient();
        $filePath = $this->getResourcePath($client, 'apple.gif');
        $content = fopen($filePath, 'r');
        $expectedContent = file_get_contents($filePath);

        $this->doTest($expectedContent, $content);
    }

    public function testStringContent ()
    {
        $client = static::createClient();
        $filePath = $this->getResourcePath($client, 'lorem.txt');
        $content = file_get_contents($filePath);

        $this->doTest($content, $content);
    }

    public function testStringResourceContent ()
    {
        $client = static::createClient();
        $filePath = $this->getResourcePath($client, 'lorem.txt');
        $content = fopen($filePath, 'r');
        $expectedContent = file_get_contents($filePath);

        $this->doTest($expectedContent, $content);
    }

    protected function doTest ($expectedContent, $content)
    {
        $request = $this->getMock('\Symfony\Component\HttpFoundation\Request');
        $request->expects($this->any())
            ->method('getContent')
            ->will($this->returnValue($content));

        $handler = new RequestContentHandler($request);
        $this->assertFalse($handler->eof());

        $foundContent = '';
        while (!$handler->eof()) {
            $foundContent .= $handler->gets();
        }

        $this->assertEquals($expectedContent, $foundContent);
        $this->assertTrue($handler->eof());
    }
} 
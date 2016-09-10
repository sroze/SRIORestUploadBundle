<?php

namespace SRIO\RestUploadBundle\Tests\Upload\Processor;

use SRIO\RestUploadBundle\Tests\Upload\AbstractUploadTestCase;
use SRIO\RestUploadBundle\Request\RequestContentHandler;

class RequestContentHandlerTest extends AbstractUploadTestCase
{
    public function testBinaryStringContent()
    {
        $client = $this->getNewClient();
        $filePath = $this->getResourcePath($client, 'apple.gif');
        $content = file_get_contents($filePath);

        $this->doTest($content, $content);
    }

    public function testBinaryResourceContent()
    {
        $client = $this->getNewClient();
        $filePath = $this->getResourcePath($client, 'apple.gif');
        $content = fopen($filePath, 'r');
        $expectedContent = file_get_contents($filePath);

        $this->doTest($expectedContent, $content);
    }

    public function testStringContent()
    {
        $client = $this->getNewClient();
        $filePath = $this->getResourcePath($client, 'lorem.txt');
        $content = file_get_contents($filePath);

        $this->doTest($content, $content);
    }

    public function testStringResourceContent()
    {
        $client = $this->getNewClient();
        $filePath = $this->getResourcePath($client, 'lorem.txt');
        $content = fopen($filePath, 'r');
        $expectedContent = file_get_contents($filePath);

        $this->doTest($expectedContent, $content);
    }

    protected function doTest($expectedContent, $content)
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

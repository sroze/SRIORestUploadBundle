<?php
namespace SRIO\RestUploadBundle\Tests\Upload;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractUploadTestCase extends WebTestCase
{
    /**
     * Assert that response has errors.
     *
     * @param Client $client
     */
    protected function assertResponseHasErrors(Client $client)
    {
        $response = $client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
    }

    /**
     * Get content of a resource.
     *
     * @param  Client            $client
     * @param $name
     * @return string
     * @throws \RuntimeException
     */
    protected function getResource(Client $client, $name)
    {
        $filePath = $this->getResourcePath($client, $name);
        if (!file_exists($filePath)) {
            throw new \RuntimeException(sprintf(
                'File %s do not exists',
                $filePath
            ));
        }

        return file_get_contents($filePath);
    }

    /**
     * Get uploaded file path.
     *
     * @param  Client $client
     * @param $name
     * @return string
     */
    protected function getUploadedFilePath(Client $client)
    {
        return $client->getContainer()->getParameter('kernel.root_dir').'/../web/uploads';
    }

    /**
     * Get resource path.
     *
     * @param  Client $client
     * @param $name
     * @return string
     */
    protected function getResourcePath(Client $client, $name)
    {
        return $client->getContainer()->getParameter('kernel.root_dir').'/../../Resources/'.$name;
    }

    /**
     * Creates a Client.
     *
     * @param array $options An array of options to pass to the createKernel class
     * @param array $server  An array of server parameters
     *
     * @return Client A Client instance
     */
    protected function getNewClient(array $options = array(), array $server = array())
    {
        $options = array_merge(array('environment' => isset($_SERVER['TEST_ENV']) ? strtolower($_SERVER['TEST_ENV']) : 'gaufrette'), $options);
        
        return static::createClient($options, $server);
    }
}

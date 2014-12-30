<?php
namespace SRIO\RestUploadBundle\Tests\Processor;

use SRIO\RestUploadBundle\Tests\Upload\AbstractUploadTestCase;

abstract class AbstractProcessorTestCase extends AbstractUploadTestCase
{
    /**
     * Call an object method, even if it is private or protected.
     *
     * @param $object
     * @param $methodName
     * @param  array $arguments
     * @return mixed
     */
    protected function callMethod($object, $methodName, array $arguments)
    {
        $method = $this->getMethod(get_class($object), $methodName);

        return $method->invokeArgs($object, $arguments);
    }

    /**
     * Get a protected method as public.
     *
     * @param $name
     * @return \ReflectionMethod
     */
    protected function getMethod($className, $name)
    {
        $class = new \ReflectionClass($className);

        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockEntityManager()
    {
        return $this->getMock('\Doctrine\ORM\EntityManager',
            array('getRepository', 'getClassMetadata', 'persist', 'flush'), array(), '', false);
    }
}

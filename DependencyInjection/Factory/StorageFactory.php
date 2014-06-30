<?php
namespace SRIO\RestUploadBundle\DependencyInjection\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class StorageFactory
{
    /**
     * Create the storage service.
     *
     */
    public function create(ContainerBuilder $container, $id, array $config)
    {
        $container
            ->setDefinition($id, new Definition('SRIO\RestUploadBundle\Storage\FileStorage'))
            ->addArgument($config['name'])
            ->addArgument(new Reference($config['filesystem']))
            ->addArgument(new Reference($config['storage_strategy']))
            ->addArgument(new Reference($config['naming_strategy']))
        ;
    }
} 
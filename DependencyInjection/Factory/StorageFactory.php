<?php

namespace SRIO\RestUploadBundle\DependencyInjection\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class StorageFactory
{
    /**
     * Create the storage service.
     */
    public function create(ContainerBuilder $container, $id, array $config)
    {
        $adapterId = $config['filesystem'].'.adapter';

        if ($config['type'] === 'gaufrette') {
            $adapterDefinition = new DefinitionDecorator('srio_rest_upload.storage.gaufrette_adapter');
            $adapterDefinition->setPublic(false);
            $adapterDefinition->replaceArgument(0, new Reference($config['filesystem']));

            $container->setDefinition($adapterId, $adapterDefinition);
        } elseif ($config['type'] === 'flysystem') {
            $adapterDefinition = new DefinitionDecorator('srio_rest_upload.storage.flysystem_adapter');
            $adapterDefinition->setPublic(false);
            $adapterDefinition->replaceArgument(0, new Reference($config['filesystem']));

            $container->setDefinition($adapterId, $adapterDefinition);
        }

        $container
            ->setDefinition($id, new Definition('SRIO\RestUploadBundle\Storage\FileStorage'))
            ->addArgument($config['name'])
            ->addArgument(new Reference($adapterId))
            ->addArgument(new Reference($config['storage_strategy']))
            ->addArgument(new Reference($config['naming_strategy']))
        ;
    }
}

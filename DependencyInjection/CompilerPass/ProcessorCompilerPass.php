<?php
namespace SRIO\RestUploadBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class ProcessorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('srio_rest_upload.upload_handler')) {
            return;
        }

        $uploadHandlerDefinition = $container->getDefinition('srio_rest_upload.upload_handler');
        $processorDefinitions = $container->findTaggedServiceIds('rest_upload.processor');

        foreach ($processorDefinitions as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                if (!array_key_exists('uploadType', $attributes)) {
                    throw new \LogicException('A "rest_upload.processor" tag must have "uploadType" attribute');
                }

                $uploadHandlerDefinition->addMethodCall('addProcessor', array($attributes['uploadType'], new Reference($id)));
            }
        }
    }
}

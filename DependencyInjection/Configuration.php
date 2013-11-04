<?php

namespace SRIO\RestUploadBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('srio_rest_upload');

        $rootNode
            ->children()
                ->scalarNode('upload_dir')->isRequired()->cannotBeEmpty()->end()
                ->arrayNode('parameters')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                    ->children()
                        ->scalarNode('uploadType')
                            ->defaultValue('uploadType')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('processors')
                    ->useAttributeAsKey('name')
                    ->defaultValue(array(
                        'simple' => 'SRIO\RestUploadBundle\Upload\Processor\SimpleUploadProcessor',
                        'multipart' => 'SRIO\RestUploadBundle\Upload\Processor\MultipartUploadProcessor',
                        'resumable' => 'SRIO\RestUploadBundle\Upload\Processor\ResumableUploadProcessor'
                    ))
                    ->prototype('scalar')->end()
                ->end()
            ->end();


        return $treeBuilder;
    }
}

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
                ->scalarNode('resumableUploadSessionEntity')->end()
                ->arrayNode('parameters')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                    ->children()
                        ->scalarNode('uploadType')
                            ->defaultValue('uploadType')
                        ->end()
                    ->end()
                ->end()
            ->end();


        return $treeBuilder;
    }
}

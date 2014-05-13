<?php

namespace SysEleven\PowerDnsBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('sys_eleven_power_dns');

        $rootNode->children()
                    ->scalarNode('entity_manager')->defaultValue('default')->end()
                    ->scalarNode('result_wrapper')->defaultValue('\SysEleven\PowerDnsBundle\Lib\ResultWrapper')->end()
                ->end();

        return $treeBuilder;
    }
}

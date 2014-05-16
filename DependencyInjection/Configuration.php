<?php
/**
 * This file is part of the SysEleven PowerDnsBundle.
 *
 * (c) SysEleven GmbH <http://www.syseleven.de/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author  M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\DependencyInjection
 */

namespace SysEleven\PowerDnsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 *
 * @author  M. Seifert <m.seifert@syseleven.de>
 * @package SysEleven\PowerDnsBundle\DependencyInjection
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
                    ->arrayNode('soa_defaults')
                                ->children()
                                    ->scalarNode('hostmaster')->defaultValue(null)->end()
                                    ->scalarNode('primary')->defaultValue(null)->end()
                                    ->scalarNode('serial')->defaultValue(0)->end()
                                    ->scalarNode('expire')->defaultValue(604800)->end()
                                    ->scalarNode('retry')->defaultValue(3600)->end()
                                    ->scalarNode('refresh')->defaultValue(10800)->end()
                                    ->scalarNode('default_ttl')->defaultValue(3600)->end()
                                ->end()
                    ->end()
                ->end();

        return $treeBuilder;
    }
}

<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\WebpackEncoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidDefinitionException;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('webpack_encore');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = method_exists($treeBuilder, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root('webpack_encore')
        ;

        $rootNode
            ->validate()
                ->ifTrue(function (array $v) {
                    return false === $v['output_path'] && empty($v['builds']);
                })
                ->thenInvalid('Default build can only be disabled if multiple entry points are defined.')
            ->end()
            ->children()
                ->scalarNode('output_path')
                    ->isRequired()
                    ->info('The path where Encore is building the assets - i.e. Encore.setOutputPath()')
                ->end()
                ->booleanNode('cache')
                    ->info('Enable caching of the entry point file(s)')
                    ->defaultFalse()
                ->end()
                ->arrayNode('builds')
                    ->useAttributeAsKey('name')
                    ->normalizeKeys(false)
                    ->scalarPrototype()
                    ->validate()
                        ->always(function ($values) {
                            if (isset($values['_default'])) {
                                throw new InvalidDefinitionException("Key '_default' can't be used as build name.");
                            }

                            return $values;
                        })
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

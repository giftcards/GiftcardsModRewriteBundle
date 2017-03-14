<?php

namespace Giftcards\ModRewriteBundle\DependencyInjection;

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
     * @codeCoverageIgnore
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('giftcards_mod_rewrite');

        $rootNode
            ->beforeNormalization()
            ->ifTrue(function ($v) {
                return empty($v['files']) && !empty($v['rewrite_listener']['files']);
            })
            ->then(function ($v) {
                $v['files'] = $v['rewrite_listener']['files'];
                return $v;
            })
            ->end()
            ->children()
                ->arrayNode('files')
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('rewrite_listener')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->booleanNode('handle_redirects')->defaultTrue()->end()
                        ->arrayNode('files')
                            ->prototype('scalar')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('router')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->scalarNode('priority')->defaultValue(0)->end()
                        ->scalarNode('controller')->defaultValue('GiftcardsModRewriteBundle:Rewrite:rewrite')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

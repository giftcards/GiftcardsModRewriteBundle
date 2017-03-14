<?php
namespace Giftcards\ModRewriteBundle\DependencyInjection;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;

class GiftcardsModRewriteExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('compiler.yml');
        $loader->load('rewriter.yml');
        $loader->load('processor.yml');

        if ($config['rewrite_listener']['enabled']) {
            $loader->load('listener.yml');
            $container->getDefinition('mod_rewrite.rewrite_listener')
                ->replaceArgument(2, $config['files'])
                ->replaceArgument(3, $config['rewrite_listener']['handle_redirects'])
            ;
        }

        if ($config['router']['enabled']) {
            $loader->load('router.yml');
            $container->getDefinition('mod_rewrite.rewrite_router')
                ->replaceArgument(2, $config['files'])
                ->replaceArgument(3, $config['router']['controller'])
                ->addTag('router', array('priority' => $config['router']['priority']))
            ;
        }

        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['WebProfilerBundle'])) {
            $loader->load('profiler.yml');
        }
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 2/23/15
 * Time: 8:14 PM
 */

namespace Giftcards\ModRewriteBundle\Tests\DependencyInjection;


use Giftcards\ModRewriteBundle\DependencyInjection\GiftcardsModRewriteExtension;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class GiftcardsModRewriteExtensionTest extends \PHPUnit_Framework_testCase
{
    /** @var  GiftcardsModRewriteExtension */
    protected $extension;

    public function setUp()
    {
        $this->extension = new GiftcardsModRewriteExtension();
    }

    public function testLoad()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', array());
        $this->extension->load(array(), $container);
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/compiler.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/rewriter.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/listener.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertNotContains(
            new FileResource(__DIR__.'/../../Resources/config/router.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertEquals(array(), $container->getDefinition('mod_rewrite.rewrite_listener')->getArgument(2));
        $this->assertTrue($container->getDefinition('mod_rewrite.rewrite_listener')->getArgument(3));
    }

    public function testLoadWithFiles()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', array());
        $this->extension->load(array(array(
            'files' => array(
                'file1',
                'file2'
            )
        )), $container);
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/compiler.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/rewriter.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/listener.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertEquals(array(
            'file1',
            'file2'
        ), $container->getDefinition('mod_rewrite.rewrite_listener')->getArgument(2));
        $this->assertTrue($container->getDefinition('mod_rewrite.rewrite_listener')->getArgument(3));
    }

    public function testLoadWithListenerFiles()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', array());
        $this->extension->load(array(array(
            'rewrite_listener' => array(
                'files' => array(
                    'file1',
                    'file2'
                )
            )
        )), $container);
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/compiler.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/rewriter.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/listener.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertEquals(array(
            'file1',
            'file2'
        ), $container->getDefinition('mod_rewrite.rewrite_listener')->getArgument(2));
        $this->assertTrue($container->getDefinition('mod_rewrite.rewrite_listener')->getArgument(3));
    }

    public function testLoadWithListenerHandleRedirectDisabled()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', array());
        $this->extension->load(array(array(
            'rewrite_listener' => array(
                'handle_redirects' => false
            )
        )), $container);
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/compiler.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/rewriter.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/listener.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertEquals(array(), $container->getDefinition('mod_rewrite.rewrite_listener')->getArgument(2));
        $this->assertFalse($container->getDefinition('mod_rewrite.rewrite_listener')->getArgument(3));
    }

    public function testLoadWhereListenerDisabled()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', array());
        $this->extension->load(array(array(
            'rewrite_listener' => array(
                'enabled' => false
            )
        )), $container);
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/compiler.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/rewriter.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertNotContains(
            new FileResource(__DIR__.'/../../Resources/config/listener.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
    }

    public function testLoadWhereRouterEnabled()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', array());
        $this->extension->load(array(array(
            'router' => array(
                'enabled' => true
            )
        )), $container);
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/compiler.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/rewriter.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/router.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertEquals(
            array(array('priority' => 0)),
            $container->getDefinition('mod_rewrite.rewrite_router')->getTag('router')
        );
        $this->assertEquals(array(), $container->getDefinition('mod_rewrite.rewrite_router')->getArgument(2));
    }

    public function testLoadWhereRouterEnabledAndHasFiles()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', array());
        $this->extension->load(array(array(
            'router' => array(
                'enabled' => true
            ),
            'files' => array(
                'file1',
                'file2'
            )
        )), $container);
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/compiler.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/rewriter.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/router.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertEquals(
            array(array('priority' => 0)),
            $container->getDefinition('mod_rewrite.rewrite_router')->getTag('router')
        );
        $this->assertEquals(array(
            'file1',
            'file2'
        ), $container->getDefinition('mod_rewrite.rewrite_listener')->getArgument(2));
    }

    public function testLoadWhereRouterEnabledAndHasFilesAndPriority()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', array());
        $this->extension->load(array(array(
            'router' => array(
                'enabled' => true,
                'priority' => 23
            ),
            'files' => array(
                'file1',
                'file2'
            )
        )), $container);
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/compiler.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/rewriter.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/router.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertEquals(
            array(array('priority' => 23)),
            $container->getDefinition('mod_rewrite.rewrite_router')->getTag('router')
        );
        $this->assertEquals(array(
            'file1',
            'file2'
        ), $container->getDefinition('mod_rewrite.rewrite_listener')->getArgument(2));
    }

    public function testLoadWhereWebProfilerBundleThere()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', array('WebProfilerBundle' => true));
        $this->extension->load(array(), $container);
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/compiler.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/rewriter.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/listener.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
        $this->assertContains(
            new FileResource(__DIR__.'/../../Resources/config/profiler.yml'),
            $container->getResources(),
            '',
            false,
            false
        );
    }
}

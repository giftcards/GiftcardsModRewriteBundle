<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 2/23/15
 * Time: 8:23 PM
 */

namespace Giftcards\ModRewriteBundle\Tests\EventListener;


use Giftcards\ModRewrite\Compiler\Configuration;
use Giftcards\ModRewrite\Compiler\Directive;
use Giftcards\ModRewrite\Compiler\Rule;
use Giftcards\ModRewrite\Result;
use Giftcards\ModRewriteBundle\EventListener\ModRewriteListener;
use Giftcards\ModRewriteBundle\Tests\Mock\Mockery\Matcher\EqualsMatcher;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;

class ModRewriteListenerTest extends \PHPUnit_Framework_testCase
{
    /** @var  ModRewriteListener */
    protected $listener;
    /** @var  ModRewriteListener */
    protected $noFilesListener;
    /** @var  ModRewriteListener */
    protected $noRedirectListener;
    /** @var  MockInterface */
    protected $rewriter;
    /** @var  MockInterface */
    protected $compiler;
    protected $fileNames;

    public function setUp()
    {
        $this->listener = new ModRewriteListener(
            $this->rewriter = \Mockery::mock('Giftcards\ModRewrite\Rewriter'),
            $this->compiler = \Mockery::mock('Giftcards\ModRewrite\Compiler\Compiler'),
            $this->fileNames = array(
                __DIR__.'/../Fixtures/rewrite1',
                __DIR__.'/../Fixtures/rewrite2',
                __DIR__.'/../Fixtures/rewrite3',
            )
        );
        $this->noFilesListener = new ModRewriteListener(
            $this->rewriter,
            $this->compiler,
            array()
        );
        $this->noRedirectListener = new ModRewriteListener(
            $this->rewriter,
            $this->compiler,
            $this->fileNames,
            false
        );
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(array(
            KernelEvents::REQUEST => array('checkModRewrites', 50)
        ), $this->listener->getSubscribedEvents());
    }

    public function testCheckModRewriteWithNoFiles()
    {
        $request = new Request();
        
        $this->noFilesListener->checkModRewrites(
            \Mockery::mock('Symfony\Component\HttpKernel\Event\GetResponseEvent')
                ->shouldReceive('getRequest')
                ->andReturn($request)
                ->getMock()
        );
    }

    public function testCheckModRewriteWithNoRewrite()
    {
        $request = new Request(array(), array(), array(), array(), array(), array('REQUEST_URI' => '/path'));

        $engine1 = new Configuration();
        $engine2 = new Configuration();
        $engine3 = new Configuration();
        
        $this->compiler
            ->shouldReceive('compile')
            ->once()
            ->with(file_get_contents($this->fileNames[0]))
            ->andReturn($engine1)
            ->getMock()
            ->shouldReceive('compile')
            ->once()
            ->with(file_get_contents($this->fileNames[1]))
            ->andReturn($engine2)
            ->getMock()
            ->shouldReceive('compile')
            ->once()
            ->with(file_get_contents($this->fileNames[2]))
            ->andReturn($engine3)
            ->getMock()
        ;
        
        $result1 = new Result('/newpath');
        $result2 = new Result('/newpath');
        $result3 = new Result('/newpath');
        $this->rewriter
            ->shouldReceive('rewrite')
            ->once()
            ->with(
                '/path',
                $request,
                $engine1
            )
            ->andReturn($result1)
            ->getMock()
            ->shouldReceive('rewrite')
            ->once()
            ->with(
                '/path',
                $request,
                $engine2
            )
            ->andReturn($result2)
            ->getMock()
            ->shouldReceive('rewrite')
            ->once()
            ->with(
                '/path',
                $request,
                $engine3
            )
            ->andReturn($result3)
            ->getMock()
        ;

        $this->listener->checkModRewrites(
            \Mockery::mock('Symfony\Component\HttpKernel\Event\GetResponseEvent')
                ->shouldReceive('getRequest')
                ->andReturn($request)
                ->getMock()
        );
        $this->assertFalse($request->attributes->has('mod_rewrite_result'));
    }

    public function testCheckModRewriteWithRewrite()
    {
        $request = new Request(array(), array(), array(), array(), array(), array('REQUEST_URI' => '/path'));

        $engine1 = new Configuration();
        $engine2 = new Configuration();
        
        $this->compiler
            ->shouldReceive('compile')
            ->once()
            ->with(file_get_contents($this->fileNames[0]))
            ->andReturn($engine1)
            ->getMock()
            ->shouldReceive('compile')
            ->once()
            ->with(file_get_contents($this->fileNames[1]))
            ->andReturn($engine2)
            ->getMock()
        ;
        
        $result1 = new Result('/newpath');
        $result2 = new Result('/newpath', new Rule(new Directive('', '', '', '', array()), array()));
        $this->rewriter
            ->shouldReceive('rewrite')
            ->once()
            ->with(
                '/path',
                $request,
                $engine1
            )
            ->andReturn($result1)
            ->getMock()
            ->shouldReceive('rewrite')
            ->once()
            ->with(
                '/path',
                $request,
                $engine2
            )
            ->andReturn($result2)
            ->getMock()
        ;

        $this->listener->checkModRewrites(
            \Mockery::mock('Symfony\Component\HttpKernel\Event\GetResponseEvent')
                ->shouldReceive('getRequest')
                ->andReturn($request)
                ->getMock()
        );
        $this->assertEquals($result2, $request->attributes->get('mod_rewrite_result'));
    }

    public function testCheckModRewriteWithRewriteAndHasRedirect()
    {
        $request = new Request(array(), array(), array(), array(), array(), array('REQUEST_URI' => '/path'));

        $engine1 = new Configuration();
        $engine2 = new Configuration();
        
        $this->compiler
            ->shouldReceive('compile')
            ->once()
            ->with(file_get_contents($this->fileNames[0]))
            ->andReturn($engine1)
            ->getMock()
            ->shouldReceive('compile')
            ->once()
            ->with(file_get_contents($this->fileNames[1]))
            ->andReturn($engine2)
            ->getMock()
        ;
        
        $result1 = new Result('/newpath');
        $result2 = new Result('/newpath', new Rule(new Directive('', '', '', '', array('R' => 301)), array()));
        $this->rewriter
            ->shouldReceive('rewrite')
            ->once()
            ->with(
                '/path',
                $request,
                $engine1
            )
            ->andReturn($result1)
            ->getMock()
            ->shouldReceive('rewrite')
            ->once()
            ->with(
                '/path',
                $request,
                $engine2
            )
            ->andReturn($result2)
            ->getMock()
        ;

        $this->listener->checkModRewrites(
            \Mockery::mock('Symfony\Component\HttpKernel\Event\GetResponseEvent')
                ->shouldReceive('getRequest')
                ->andReturn($request)
                ->getMock()
                ->shouldReceive('setResponse')
                ->with(new EqualsMatcher(new RedirectResponse('/newpath', 301)))
                ->getMock()
        );
        $this->assertEquals($result2, $request->attributes->get('mod_rewrite_result'));
    }

    public function testCheckModRewriteWithRewriteAndHasRedirectButListenerHasRedirectsDisabled()
    {
        $request = new Request(array(), array(), array(), array(), array(), array('REQUEST_URI' => '/path'));

        $engine1 = new Configuration();
        $engine2 = new Configuration();
        
        $this->compiler
            ->shouldReceive('compile')
            ->once()
            ->with(file_get_contents($this->fileNames[0]))
            ->andReturn($engine1)
            ->getMock()
            ->shouldReceive('compile')
            ->once()
            ->with(file_get_contents($this->fileNames[1]))
            ->andReturn($engine2)
            ->getMock()
        ;
        
        $result1 = new Result('/newpath');
        $result2 = new Result('/newpath', new Rule(new Directive('', '', '', '', array('R' => 301)), array()));
        $this->rewriter
            ->shouldReceive('rewrite')
            ->once()
            ->with(
                '/path',
                $request,
                $engine1
            )
            ->andReturn($result1)
            ->getMock()
            ->shouldReceive('rewrite')
            ->once()
            ->with(
                '/path',
                $request,
                $engine2
            )
            ->andReturn($result2)
            ->getMock()
        ;

        $this->noRedirectListener->checkModRewrites(
            \Mockery::mock('Symfony\Component\HttpKernel\Event\GetResponseEvent')
                ->shouldReceive('getRequest')
                ->andReturn($request)
                ->getMock()
        );
        $this->assertEquals($result2, $request->attributes->get('mod_rewrite_result'));
    }
}

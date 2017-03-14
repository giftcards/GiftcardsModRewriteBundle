<?php
/**
 * Created by PhpStorm.
 * User: ydera00
 * Date: 3/14/17
 * Time: 5:58 PM
 */

namespace Giftcards\ModRewriteBundle\Tests\Routing;

use Giftcards\ModRewrite\Compiler\Configuration;
use Giftcards\ModRewrite\Compiler\Directive;
use Giftcards\ModRewrite\Compiler\Rule;
use Giftcards\ModRewrite\Result;
use Giftcards\ModRewrite\Tests\TestCase;
use Giftcards\ModRewriteBundle\Routing\Router;
use Giftcards\ModRewriteBundle\Tests\Mock\Mockery\Matcher\EqualsMatcher;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

class RouterTest extends TestCase
{
    /** @var  Router */
    protected $router;
    /** @var  Router */
    protected $noFilesRouter;
    /** @var  MockInterface */
    protected $rewriter;
    /** @var  MockInterface */
    protected $compiler;
    protected $fileNames;
    protected $controller;

    public function setUp()
    {
        $this->router = new Router(
            $this->rewriter = \Mockery::mock('Giftcards\ModRewrite\Rewriter'),
            $this->compiler = \Mockery::mock('Giftcards\ModRewrite\Compiler\Compiler'),
            $this->fileNames = array(
                __DIR__.'/../Fixtures/rewrite1',
                __DIR__.'/../Fixtures/rewrite2',
                __DIR__.'/../Fixtures/rewrite3',
            ),
            $this->controller = $this->getFaker()->unique()->word
        );
        $this->noFilesRouter = new Router(
            $this->rewriter,
            $this->compiler,
            array()
        );
    }

    public function matchProvider()
    {
        return array(array(new RequestContext()), array(null));
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\ResourceNotFoundException
     * @dataProvider matchProvider
     */
    public function testMatchWithNoFiles(RequestContext $requestContext = null)
    {
        $request = new Request(array(), array(), array(), array(), array(), array('REQUEST_URI' => '/path%20hello'));

        if ($requestContext) {
            $this->noFilesRouter->setContext($requestContext->fromRequest($request));
            $this->assertSame($requestContext, $this->noFilesRouter->getContext());
        }

        $this->noFilesRouter->match($request->getPathInfo());
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\ResourceNotFoundException
     * @dataProvider matchProvider
     */
    public function testMatchWithNoRewrite(RequestContext $requestContext = null)
    {
        $request = Request::create('http://www.hello.com/path%20hello');
        $expectRequest = Request::create($request->getPathInfo());

        if ($requestContext) {
            $this->router->setContext($requestContext->fromRequest($request));
            $this->assertSame($requestContext, $this->router->getContext());
            $expectRequest = Request::create('http://www.hello.com/path%20hello');
        }
        
        $expectRequest->getPathInfo();

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
                '/path hello',
                new EqualsMatcher($expectRequest),
                $engine1
            )
            ->andReturn($result1)
            ->getMock()
            ->shouldReceive('rewrite')
            ->once()
            ->with(
                '/path hello',
                new EqualsMatcher($expectRequest),
                $engine2
            )
            ->andReturn($result2)
            ->getMock()
            ->shouldReceive('rewrite')
            ->once()
            ->with(
                '/path hello',
                new EqualsMatcher($expectRequest),
                $engine3
            )
            ->andReturn($result3)
            ->getMock()
        ;

        $this->router->match($request->getPathInfo());
    }

    /**
     * @dataProvider matchProvider
     */
    public function testMatchWithRewrite(RequestContext $requestContext = null)
    {
        $request = Request::create('http://www.hello.com/path');
        $expectRequest = Request::create($request->getPathInfo());

        if ($requestContext) {
            $this->router->setContext($requestContext->fromRequest($request));
            $this->assertSame($requestContext, $this->router->getContext());
            $expectRequest = Request::create('http://www.hello.com/path');
        }
        
        $expectRequest->getPathInfo();

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
        $routeName = $this->getFaker()->unique()->sentence();
        $result2 = new Result('/newpath', new Rule(new Directive($routeName, '', '', '', array()), array()));
        $this->rewriter
            ->shouldReceive('rewrite')
            ->once()
            ->with(
                '/path',
                new EqualsMatcher($expectRequest, 0, 10, false, false, true),
                $engine1
            )
            ->andReturn($result1)
            ->getMock()
            ->shouldReceive('rewrite')
            ->once()
            ->with(
                '/path',
                new EqualsMatcher($expectRequest),
                $engine2
            )
            ->andReturn($result2)
            ->getMock()
        ;

        $this->assertEquals(array(
            '_route' => $routeName,
            '_controller' => $this->controller,
            'result' => $result2
        ), $this->router->match($request->getPathInfo()));
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function testMatchRequestWithNoFiles()
    {
        $request = new Request();

        $this->noFilesRouter->matchRequest(
            $request
        );
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function testMatchRequestWithNoRewrite()
    {
        $request = new Request(array(), array(), array(), array(), array(), array('REQUEST_URI' => '/path%20hello'));

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
                '/path hello',
                $request,
                $engine1
            )
            ->andReturn($result1)
            ->getMock()
            ->shouldReceive('rewrite')
            ->once()
            ->with(
                '/path hello',
                $request,
                $engine2
            )
            ->andReturn($result2)
            ->getMock()
            ->shouldReceive('rewrite')
            ->once()
            ->with(
                '/path hello',
                $request,
                $engine3
            )
            ->andReturn($result3)
            ->getMock()
        ;

        $this->router->matchRequest($request);
    }

    public function testMatchRequestWithRewrite()
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
        $routeName = $this->getFaker()->unique()->sentence();
        $result2 = new Result('/newpath', new Rule(new Directive($routeName, '', '', '', array()), array()));
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

        $this->assertEquals(array(
            '_route' => $routeName,
            '_controller' => $this->controller,
            'result' => $result2
        ), $this->router->matchRequest($request));
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGenerate()
    {
        $this->router->generate($this->getFaker()->unique()->word);
    }

    public function testGetRouteCollection()
    {
        $this->assertEquals(new RouteCollection(), $this->router->getRouteCollection());
    }
}

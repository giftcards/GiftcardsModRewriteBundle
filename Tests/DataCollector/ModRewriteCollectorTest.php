<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 2/24/15
 * Time: 11:43 PM
 */

namespace Giftcards\ModRewriteBundle\Tests\DataCollector;


use Giftcards\ModRewrite\Compiler\Directive;
use Giftcards\ModRewrite\Compiler\Rule;
use Giftcards\ModRewrite\Result;
use Giftcards\ModRewriteBundle\DataCollector\ModRewriteCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ModRewriteCollectorTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ModRewriteCollector */
    protected $collector;

    public function setUp()
    {
        $this->collector = new ModRewriteCollector();
    }
    
    public function testGetName()
    {
        $this->assertEquals('mod_rewrite', $this->collector->getName());
    }

    public function testCollect()
    {
        $request = new Request();
        $response = new Response();
        $this->collector->collect($request, $response);
        $this->assertFalse($this->collector->hasResult());
        $result = new Result(
            'url',
            new Rule(new Directive('', '', '', '', array()), array())
        );
        $request->attributes->set('mod_rewrite_result', $result);
        $this->collector->collect($request, $response);
        $this->assertTrue($this->collector->hasResult());
        $this->assertSame($result, $this->collector->getResult());
        $this->assertSame($result->getUrl(), $this->collector->getNewUrl());
        $this->assertSame($result->getMatchedRule(), $this->collector->getRule());
    }
}

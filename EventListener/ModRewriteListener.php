<?php
/**
 * Created by PhpStorm.
 * User: jderay
 * Date: 2/17/15
 * Time: 6:21 PM
 */

namespace Giftcards\ModRewriteBundle\EventListener;


use Giftcards\ModRewrite\Compiler\Compiler;
use Giftcards\ModRewrite\Rewriter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ModRewriteListener implements EventSubscriberInterface
{
    protected $rewriter;
    protected $compiler;
    protected $parser;
    protected $fileNames;
    protected $handleRedirects;
    
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('checkModRewrites', 50)
        );
    }

    public function __construct(
        Rewriter $rewriter,
        Compiler $compiler,
        $fileNames,
        $handleRedirects = true
    ) {
        $this->rewriter = $rewriter;
        $this->compiler = $compiler;
        $this->fileNames = $fileNames;
        $this->handleRedirects = $handleRedirects;
    }

    public function checkModRewrites(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $result = null;
        
        foreach ($this->fileNames as $fileName) {

            $result = $this->rewriter->rewrite(
                rawurldecode($request->getPathInfo()),
                $request,
                $this->compiler->compile(file_get_contents($fileName))
            );

            if ($result->getMatchedRule()) {

                break;
            }
        }

        if (!$result || !$result->getMatchedRule()) {

            return;
        }

        $request->attributes->set('mod_rewrite_result', $result);

        $flags = $result->getMatchedRule()->getRewrite()->getFlags();
        
        if (!empty($flags['redirect']) || !empty($flags['R']) && $this->handleRedirects) {

            $statusCode = !empty($flags['redirect']) ? $flags['redirect'] : $flags['R'];
            $event->setResponse(new RedirectResponse($result->getUrl(), $statusCode));
            return;
        }
    }
}
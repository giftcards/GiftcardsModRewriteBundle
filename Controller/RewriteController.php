<?php
/**
 * Created by PhpStorm.
 * User: ydera00
 * Date: 3/14/17
 * Time: 9:59 AM
 */

namespace Giftcards\ModRewriteBundle\Controller;

use Giftcards\ModRewrite\Result;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RewriteController extends Controller
{
    public function rewriteAction(Request $request, Result $result)
    {
        $processed = $this->get('mod_rewrite.processor')->process($request, $result);

        if ($processed instanceof Response) {
            return $processed;
        }

        $processed->attributes->remove('_controller');
        return $this->get('http_kernel')->handle($processed, HttpKernelInterface::SUB_REQUEST);
    }
}

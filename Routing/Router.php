<?php
/**
 * Created by PhpStorm.
 * User: ydera00
 * Date: 3/14/17
 * Time: 9:42 AM
 */

namespace Giftcards\ModRewriteBundle\Routing;

use Giftcards\ModRewrite\Compiler\Compiler;
use Giftcards\ModRewrite\Rewriter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class Router implements RouterInterface, RequestMatcherInterface
{
    protected $rewriter;
    protected $compiler;
    protected $fileNames;
    /** @var  RequestContext|null */
    protected $context;
    protected $controller;

    public function __construct(
        Rewriter $rewriter,
        Compiler $compiler,
        $fileNames,
        $controller = 'GiftcardsModRewriteBundle:Rewrite:rewrite'
    ) {
        $this->rewriter = $rewriter;
        $this->compiler = $compiler;
        $this->fileNames = $fileNames;
        $this->controller = $controller;
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Gets the RouteCollection instance associated with this Router.
     *
     * @return RouteCollection A RouteCollection instance
     */
    public function getRouteCollection()
    {
        return new RouteCollection();
    }

    /**
     * Generates a URL or path for a specific route based on the given parameters.
     *
     * Parameters that reference placeholders in the route pattern will substitute them in the
     * path or host. Extra params are added as query string to the URL.
     *
     * When the passed reference type cannot be generated for the route because it requires a different
     * host or scheme than the current one, the method will return a more comprehensive reference
     * that includes the required params. For example, when you call this method with $referenceType = ABSOLUTE_PATH
     * but the route requires the https scheme whereas the current scheme is http, it will instead return an
     * ABSOLUTE_URL with the https scheme and the current host. This makes sure the generated URL matches
     * the route in any case.
     *
     * If there is no route with the given name, the generator must throw the RouteNotFoundException.
     *
     * @param string $name The name of the route
     * @param mixed $parameters An array of parameters
     * @param bool|string $referenceType The type of reference to be generated (one of the constants)
     *
     * @return string The generated URL
     *
     * @throws RouteNotFoundException              If the named route doesn't exist
     * @throws MissingMandatoryParametersException When some parameters are missing that are mandatory for the route
     * @throws InvalidParameterException           When a parameter value for a placeholder is not correct because
     *                                             it does not match the requirement
     *
     * @api
     */
    public function generate(
        $name,
        $parameters = array(),
        $referenceType = self::ABSOLUTE_PATH
    ) {
        throw new RouteNotFoundException(sprintf(
            'Unable to generate a URL for the named route "%s" as such route does not exist.',
            $name
        ));
    }

    /**
     * Tries to match a URL path with a set of routes.
     *
     * If the matcher can not find information, it must throw one of the exceptions documented
     * below.
     *
     * @param string $pathinfo The path info to be parsed (raw format, i.e. not urldecoded)
     *
     * @return array An array of parameters
     *
     * @throws ResourceNotFoundException If the resource could not be found
     * @throws MethodNotAllowedException If the resource was found but the request method is not allowed
     *
     * @api
     */
    public function match($pathinfo)
    {
        $method = $this->context ? $this->context->getMethod() : 'GET';
        $parameters = $this->context ? $this->context->getParameters() : array();
        $pathinfo = $this->context ? sprintf(
            '%s://%s%s%s',
            $this->context->getScheme(),
            $this->context->getHost(),
            $pathinfo,
            $this->context->getQueryString() ? '?' . $this->context->getQueryString() : ''
        ) : $pathinfo;

        return $this->matchRequest(Request::create(
            $pathinfo,
            $method,
            $parameters
        ));
    }

    /**
     * Tries to match a request with a set of routes.
     *
     * If the matcher can not find information, it must throw one of the exceptions documented
     * below.
     *
     * @param Request $request The request to match
     *
     * @return array An array of parameters
     *
     * @throws ResourceNotFoundException If no matching resource could be found
     * @throws MethodNotAllowedException If a matching resource was found but the request method is not allowed
     */
    public function matchRequest(Request $request)
    {
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
            throw new ResourceNotFoundException();
        }

        return  array(
            '_route' => $result->getMatchedRule()->getRewrite()->getContent(),
            '_controller' => $this->controller,
            'result' => $result
        );
    }
}
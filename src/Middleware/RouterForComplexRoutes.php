<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * This Router uses the super fast symfony 4 router. You can configure your routes using config/routes.yaml.
 * The Router is always the last middleware in the stack.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class RouterForComplexRoutes implements MiddlewareInterface
{
    const CONTROLLER_METHOD_SEPARATOR = '::';
    
    private $matcher;
    private $container;

    public function __construct(ContainerInterface $container, RequestMatcherInterface $matcher)
    {
        if (!interface_exists(RouterInterface::class)) {
            throw new \RuntimeException(sprintf('Please run "composer require symfony/routing" to use "%s"', __CLASS__));
        }

        $this->container = $container;
        $this->matcher = $matcher;
    }

    public function __invoke(Request $request, RequestHandlerInterface $handler): Response
    {
        try {
            $parameters = $this->matcher->matchRequest($request);
        } catch (ResourceNotFoundException $e) {
            return new Response('Not Found', 404);
        }

        // We expect $parameters['_controller'] to contain a string on format "ControllerClass::action"
        if (!strpos($parameters['_controller'], self::CONTROLLER_METHOD_SEPARATOR)) {
            throw new \LogicException(sprintf('Invalid route config for route "%s". "%s" expected.', $parameters['_route'], self::CONTROLLER_METHOD_SEPARATOR));
        }
        
        list($class, $method) = explode('::', $parameters['_controller'], 2);
        if (!$this->container->has($class)) {
            throw new \LogicException(sprintf('Invalid route config for route "%s". Controller "%s" not found.', $parameters['_route'], $class));
        }

        $controller = [$this->container->get($class), $method];
        $arguments = $this->getArguments($request, $controller, $parameters);

        return $controller(...$arguments);
    }

    private function getArguments(Request $request, array $controller, array $parameters)
    {
        $reflection = new \ReflectionMethod($controller[0], $controller[1]);
        foreach ($reflection->getParameters() as $param) {
            if (isset($parameters[$param->getName()])) {
                $arguments[] = $parameters[$param->getName()];
                continue;
            }

            if ($type = $param->getType()) {
                if (Request::class === $type->getName()) {
                    $arguments[] = $request;
                }
            }
        }

        return $arguments ?? [];
    }
}

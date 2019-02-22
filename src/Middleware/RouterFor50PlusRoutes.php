<?php

declare(strict_types=1);

namespace App\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

/**
 * The Router is always the last middleware in the stack.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class RouterFor50PlusRoutes implements MiddlewareInterface
{
    private $matcher;
    private $container;

    public function __construct(RequestMatcherInterface $matcher)
    {
        $this->matcher = $matcher;
    }

    public function setContainer($container): void
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, RequestHandlerInterface $handler): Response
    {
        $parameters = $this->matcher->matchRequest($request);
        list($class, $method) = explode('::', $parameters['_controller'], 2);
        if (!$this->container->has($class)) {
            throw new \LogicException(sprintf('Invalid route config for route "%s"', $parameters['route']));
        }

        $controller = [$this->container->get($class), $method];
        $arguments = $this->getArguments($request, $controller, $parameters);

        return $controller(...$arguments);
    }

    private function getArguments(Request $request, array $controller, array $parameters) {
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

        return $arguments;
    }
}

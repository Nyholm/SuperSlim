<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Controller\DemoController;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This Router is just a collection of if statements. Use this when you have less then 20-30 routes.
 * The Router is always the last middleware in the stack.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Router implements MiddlewareInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, RequestHandlerInterface $handler): Response
    {
        $uri = $request->getRequestUri();

        if ('/' === $uri) {
            return $this->container->get(DemoController::class)->index($request, 'xxx');
        }

        // TODO Add more if-statements for all your routes

        return new Response('Not Found', 404);
    }
}

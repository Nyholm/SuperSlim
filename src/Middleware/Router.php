<?php

declare(strict_types=1);

namespace App\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The Router is always the last middleware in the stack.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Router implements MiddlewareInterface
{
    public function __invoke(Request $request, RequestHandlerInterface $handler): Response
    {
        $uri = $request->getBaseUrl();

        switch ($uri) {
            case '':
                $response = (new \App\Controller\DemoController())->index($request);
                break;
            default:
                $response = new Response('Not Found', 404);
                break;
        }

        return $response;
    }
}

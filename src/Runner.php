<?php

declare(strict_types=1);

namespace App;

use App\Middleware\MiddlewareInterface;
use App\Middleware\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A simple implementation of a middleware runner. It will run all middleware in
 * order. The last middleware in the chain (usually the Router) will not call RequestHandlerInterface::handle(),
 * it should create a new response and return it.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Runner implements RequestHandlerInterface
{
    /** @var MiddlewareInterface[] */
    private $queue;

    public function __construct(MiddlewareInterface ...$queue)
    {
        $this->queue = $queue;
    }

    public function handle(Request $request): Response
    {
        $middleware = array_shift($this->queue);
        if (null === $middleware) {
            throw new \LogicException('The last middleware did not return a response. Did you really add the router as last middleware?');
        }

        return $middleware($request, $this);
    }
}

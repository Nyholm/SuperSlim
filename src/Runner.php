<?php

declare(strict_types=1);

namespace App;

use App\Middleware\MiddlewareInterface;
use App\Middleware\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Runner implements RequestHandlerInterface
{
    /** @var MiddlewareInterface[] */
    private $queue;

    public function __construct(array $queue)
    {
        $this->queue = $queue;
    }

    public function handle(Request $request): Response
    {
        $middleware = array_shift($this->queue);
        if (null === $middleware) {
            throw new \LogicException('The last middleware did not return a response');
        }

        return $middleware($request, $this);
    }
}

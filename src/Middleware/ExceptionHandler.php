<?php

declare(strict_types=1);

namespace App\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExceptionHandler implements MiddlewareInterface
{
    private $environment;

    public function __construct(string $environment)
    {
        $this->environment = $environment;
    }

    public function __invoke(Request $request, RequestHandlerInterface $handler): Response
    {
        try {
            $response = $handler->handle($request);
        } catch (\Throwable $exception) {
            if ('prod' === $this->environment) {
                $response = new Response($exception->getMessage(), 500);
            } else {
                throw $exception;
            }
        }

        return $response;
    }
}

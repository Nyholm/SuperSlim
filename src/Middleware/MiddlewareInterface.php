<?php

declare(strict_types=1);

namespace App\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * All middleware should implement this interface. It is similar to PSR-15.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
interface MiddlewareInterface
{
    public function __invoke(Request $request, RequestHandlerInterface $handler): Response;
}

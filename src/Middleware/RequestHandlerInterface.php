<?php

declare(strict_types=1);

namespace App\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Used for type-hinting in MiddlewareInterface. See this as "the next middleware in the chain".
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
interface RequestHandlerInterface
{
    public function handle(Request $request): Response;
}

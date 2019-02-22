<?php

declare(strict_types=1);

namespace App\Middleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Cache request to improve performance
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Cache implements MiddlewareInterface
{
    private $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function __invoke(Request $request, RequestHandlerInterface $handler): Response
    {
        $cacheKey = sha1($request->getUri());
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($handler, $request) {
            $item->expiresAfter(3600);

            return $handler->handle($request);
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * A cache that does not remember anything.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class VoidCache implements CacheInterface
{
    public function get(string $key, callable $callback, float $beta = null, array &$metadata = null)
    {
        return $callback(new class() implements ItemInterface {
            public function getKey()
            {
                return '';
            }

            public function get()
            {
                return null;
            }

            public function isHit()
            {
                return false;
            }

            public function set($value)
            {
            }

            public function expiresAt($expiration)
            {
            }

            public function expiresAfter($time)
            {
            }

            public function tag($tags): ItemInterface
            {
                return $this;
            }

            public function getMetadata(): array
            {
                return [];
            }
        });
    }

    public function delete(string $key): bool
    {
        return true;
    }
}

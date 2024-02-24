<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Implementation;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class NotWorkingCachePool implements CacheItemPoolInterface
{
    public function getItem($key): CacheItemInterface
    {
        return new NotWorkingCacheItem($key);
    }

    public function getItems(array $keys = []): array
    {
        return [];
    }

    public function hasItem($key): bool
    {
        return false;
    }

    public function clear(): bool
    {
        return false;
    }

    public function deleteItem($key): bool
    {
        return false;
    }

    public function deleteItems(array $keys): bool
    {
        return false;
    }

    public function save(CacheItemInterface $item): bool
    {
        return false;
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        return false;
    }

    public function commit(): bool
    {
        return false;
    }
}

<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2022 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Implementation;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class NotWorkingCachePool implements CacheItemPoolInterface
{
    /**
     * {@inheritdoc}
     */
    public function getItem($key): CacheItemInterface
    {
        return new NotWorkingCacheItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = []): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function commit(): bool
    {
        return false;
    }
}

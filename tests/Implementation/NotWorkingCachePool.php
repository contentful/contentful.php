<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Implementation;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class NotWorkingCachePool implements CacheItemPoolInterface
{
    public function getItem($key)
    {
        return new NotWorkingCacheItem($key);
    }

    public function getItems(array $keys = [])
    {
        return [];
    }

    public function hasItem($key)
    {
        return \false;
    }

    public function clear()
    {
        return \false;
    }

    public function deleteItem($key)
    {
        return \false;
    }

    public function deleteItems(array $keys)
    {
        return \false;
    }

    public function save(CacheItemInterface $item)
    {
        return \false;
    }

    public function saveDeferred(CacheItemInterface $item)
    {
        return \false;
    }

    public function commit()
    {
        return \false;
    }
}

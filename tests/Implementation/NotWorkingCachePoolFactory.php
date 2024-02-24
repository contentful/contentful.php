<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Implementation;

use Contentful\Delivery\Cache\CacheItemPoolFactoryInterface;
use Psr\Cache\CacheItemPoolInterface;

class NotWorkingCachePoolFactory implements CacheItemPoolFactoryInterface
{
    public function getCacheItemPool(string $api, string $spaceId, string $environmentId): CacheItemPoolInterface
    {
        return new NotWorkingCachePool();
    }
}

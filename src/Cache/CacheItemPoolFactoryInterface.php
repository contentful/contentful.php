<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Cache;

use Psr\Cache\CacheItemPoolInterface;

/**
 * CacheItemPoolFactoryInterface.
 *
 * This interface must be implemented by a factory
 * in order to be compatible with CLI commands for warming up
 * and clearing the cache.
 */
interface CacheItemPoolFactoryInterface
{
    /**
     * Returns a PSR-6 CacheItemPoolInterface object.
     * The method receives two parameters, which can be used
     * for things like using a different directory in the filesystem,
     * but the implementation can simply not use them if they're not necessary.
     *
     * @param string $api           A string representation of the API in use,
     *                              it's the result of calling $client->getApi()
     * @param string $spaceId       The ID of the space
     * @param string $environmentId The ID of the environment
     */
    public function getCacheItemPool(string $api, string $spaceId, string $environmentId): CacheItemPoolInterface;
}

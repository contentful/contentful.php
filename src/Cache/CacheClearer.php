<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Cache;

use Contentful\Delivery\SystemProperties;

/**
 * CacheClearer class.
 *
 * Use this class to clear the needed cache information from a
 * PSR-6 compatible pool.
 */
class CacheClearer extends BaseCacheHandler
{
    /**
     * @param bool $cacheContent
     *
     * @return bool
     */
    public function clear($cacheContent = \false): bool
    {
        $instanceRepository = $this->client->getInstanceRepository();

        $keys = [];
        foreach ($this->fetchResources($cacheContent) as $resource) {
            /** @var SystemProperties $sys */
            $sys = $resource->getSystemProperties();
            $keys[] = $instanceRepository->generateCacheKey($sys->getType(), $sys->getId(), $sys->getLocale());
        }

        return $this->cacheItemPool->deleteItems($keys);
    }
}

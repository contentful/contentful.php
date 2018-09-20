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
use function GuzzleHttp\json_encode as guzzle_json_encode;

/**
 * CacheWarmer class.
 *
 * Use this class to save the needed cache information in a
 * PSR-6 compatible pool.
 */
class CacheWarmer extends BaseCacheHandler
{
    /**
     * @param bool $cacheContent
     *
     * @return bool
     */
    public function warmUp($cacheContent = \false): bool
    {
        $instanceRepository = $this->client->getInstanceRepository();

        foreach ($this->fetchResources($cacheContent) as $resource) {
            /** @var SystemProperties $sys */
            $sys = $resource->getSystemProperties();
            $key = $instanceRepository->generateCacheKey($sys->getType(), $sys->getId(), $sys->getLocale());

            $item = $this->cacheItemPool->getItem($key);
            $item->set(guzzle_json_encode($resource));

            $this->cacheItemPool->saveDeferred($item);
        }

        return $this->cacheItemPool->commit();
    }
}

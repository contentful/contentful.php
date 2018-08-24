<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

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
    public function warmUp($cacheContent = false)
    {
        $api = $this->client->getApi();
        $instanceRepository = $this->client->getInstanceRepository();
        $previous = $this->toggleAutoWarmup($instanceRepository, false);

        foreach ($this->fetchResources($cacheContent) as $resource) {
            /** @var SystemProperties $sys */
            $sys = $resource->getSystemProperties();
            $key = $instanceRepository->generateCacheKey($api, $sys->getType(), $sys->getId(), $sys->getLocale());

            $item = $this->cacheItemPool->getItem($key);
            $item->set(guzzle_json_encode($resource));

            $this->cacheItemPool->saveDeferred($item);
        }

        $this->toggleAutoWarmup($instanceRepository, $previous);

        return $this->cacheItemPool->commit();
    }
}

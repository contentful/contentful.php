<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Cache\CacheClearer;
use Contentful\Delivery\Cache\CacheWarmer;
use Contentful\Tests\Delivery\End2EndTestCase;

class CacheTest extends End2EndTestCase
{
    /**
     * @vcr e2e_cache_warmup_clear.json
     */
    public function testCacheWarmupClear()
    {
        self::$cache->clear();

        $client = $this->getClient('cfexampleapi');

        $warmer = new CacheWarmer($client, self::$cache);
        $clearer = new CacheClearer(self::$cache);

        $warmer->warmUp();

        $cacheItem = self::$cache->getItem('space');
        $this->assertTrue($cacheItem->isHit());

        $rawSpace = \json_decode($cacheItem->get(), true);
        $this->assertSame('cfexampleapi', $rawSpace['sys']['id']);

        $clearer->clear();
        $this->assertFalse(self::$cache->hasItem('space'));

        self::$cache->clear();
    }

    /**
     * @vcr e2e_cache_empty.json
     */
    public function testApiWorksWithEmptyCache()
    {
        self::$cache->clear();

        $client = $this->getClient('cfexampleapi_cache');

        $this->assertSame('cfexampleapi', $client->getSpace()->getId());
        $this->assertSame('cat', $client->getContentType('cat')->getId());

        self::$cache->clear();
    }

    /**
     * @vcr e2e_cache_access_cached.json
     */
    public function testAccessCachedContent()
    {
        self::$cache->clear();

        $client = $this->getClient('cfexampleapi');

        $warmer = new CacheWarmer($client, self::$cache);
        $warmer->warmUp();

        $client = $this->getClient('cfexampleapi_cache');

        $this->assertSame('cfexampleapi', $client->getSpace()->getId());
        $this->assertSame('cat', $client->getContentType('cat')->getId());

        self::$cache->clear();
    }
}

<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Cache\CacheClearer;
use Contentful\Delivery\Cache\CacheWarmer;
use Contentful\Tests\Delivery\End2EndTestCase;
use Symfony\Component\Filesystem\Filesystem;

class CacheTest extends End2EndTestCase
{
    private function clearCacheDir()
    {
        (new Filesystem())
            ->remove(self::$cacheDir);
    }

    /**
     * @vcr e2e_cache_warmup_clear.json
     */
    public function testCacheWarmupClear()
    {
        $this->clearCacheDir();

        $fs = new Filesystem();

        $client = $this->getClient('cfexampleapi');

        $warmer = new CacheWarmer($client);
        $clearer = new CacheClearer('cfexampleapi');

        $warmer->warmUp(self::$cacheDir);
        $this->assertTrue($fs->exists(self::$cacheDir.'/cfexampleapi'));
        $this->assertTrue($fs->exists(self::$cacheDir.'/cfexampleapi/space.json'));

        $rawSpace = \json_decode(\file_get_contents(self::$cacheDir.'/cfexampleapi/space.json'), true);
        $this->assertSame('cfexampleapi', $rawSpace['sys']['id']);

        $clearer->clear(self::$cacheDir);
        $this->assertFalse($fs->exists(self::$cacheDir.'/cfexampleapi'));

        $this->clearCacheDir();
    }

    /**
     * @vcr e2e_cache_empty.json
     */
    public function testApiWorksWithEmptyCache()
    {
        $this->clearCacheDir();

        $client = $this->getClient('cfexampleapi_cache', true);

        $this->assertSame('cfexampleapi', $client->getSpace()->getId());
        $this->assertSame('cat', $client->getContentType('cat')->getId());

        $this->clearCacheDir();
    }

    /**
     * @vcr e2e_cache_access_cached.json
     */
    public function testAccessCachedContent()
    {
        $this->clearCacheDir();

        $client = $this->getClient('cfexampleapi');

        $warmer = new CacheWarmer($client);
        $warmer->warmUp(self::$cacheDir);

        $client = $this->getClient('cfexampleapi_cache', true);

        $this->assertSame('cfexampleapi', $client->getSpace()->getId());
        $this->assertSame('cat', $client->getContentType('cat')->getId());

        $this->clearCacheDir();
    }
}

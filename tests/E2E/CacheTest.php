<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Cache\CacheClearer;
use Contentful\Delivery\Cache\CacheWarmer;
use Contentful\Delivery\Client;
use Symfony\Component\Filesystem\Filesystem;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @vcr e2e_cache_warmup_clear.json
     */
    public function testCacheWarmupClear()
    {
        $cacheDir = __DIR__ . '/../../build/cache';
        $spaceId = 'cfexampleapi';
        $fs = new Filesystem;

        // To be safe, we start with an empty state
        $fs->remove($cacheDir);

        $client = new Client('b4c0n73n7fu1', $spaceId);
        $warmer = new CacheWarmer($client);
        $clearer = new CacheClearer($spaceId);

        $warmer->warmUp($cacheDir);
        $this->assertTrue($fs->exists($cacheDir . '/' . $spaceId));
        $this->assertTrue($fs->exists($cacheDir . '/' . $spaceId . '/space.json'));

        $rawSpace = json_decode(file_get_contents($cacheDir . '/' . $spaceId . '/space.json'), true);
        $this->assertEquals($spaceId, $rawSpace['sys']['id']);

        $clearer->clear($cacheDir);
        $this->assertFalse($fs->exists($cacheDir . '/' . $spaceId));
    }

    /**
     * @vcr e2e_cache_empty.json
     */
    public function testApiWorksWithEmptyCache()
    {
        $cacheDir = __DIR__ . '/../../build/cache';
        $spaceId = 'cfexampleapi';
        $fs = new Filesystem;

        // MAke extra sure there's nothing cached
        $fs->remove($cacheDir);

        $client = new Client('b4c0n73n7fu1', $spaceId, false, null, ['cacheDir' => $cacheDir]);
        $this->assertEquals($spaceId, $client->getSpace()->getId());
        $this->assertEquals('cat', $client->getContentType('cat')->getId());
    }

    /**
     * @vcr e2e_cache_access_cached.json
     */
    public function testAccessCachedContent()
    {
        $cacheDir = __DIR__ . '/../../build/cache';
        $spaceId = 'cfexampleapi';
        $fs = new Filesystem;

        // To be safe, we start with an empty state
        $fs->remove($cacheDir);

        $client = new Client('b4c0n73n7fu1', $spaceId);
        $warmer = new CacheWarmer($client);
        $warmer->warmUp($cacheDir);

        $client = new Client('b4c0n73n7fu1', $spaceId, false, null, ['cacheDir' => $cacheDir]);
        $this->assertEquals($spaceId, $client->getSpace()->getId());
        $this->assertEquals('cat', $client->getContentType('cat')->getId());

        // Don't forget to clean up
        $fs->remove($cacheDir);
    }
}

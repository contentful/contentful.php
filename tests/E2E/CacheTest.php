<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\E2E;

use Contentful\Delivery\Cache\CacheClearer;
use Contentful\Delivery\Cache\CacheWarmer;
use Contentful\Delivery\Query;
use Contentful\Tests\Delivery\TestCase;

use function GuzzleHttp\json_decode as guzzle_json_decode;

class CacheTest extends TestCase
{
    /**
     * @vcr cache_warmup_clear.json
     */
    public function testWarmupClear()
    {
        self::$cache->clear();

        $client = $this->getClient('default');
        $resourcePool = $client->getResourcePool();

        $warmer = new CacheWarmer($client, $client->getResourcePool(), self::$cache);
        $clearer = new CacheClearer($client, $client->getResourcePool(), self::$cache);

        $warmer->warmUp();

        $cacheItem = self::$cache->getItem(
            $resourcePool->generateKey('Space', 'cfexampleapi')
        );
        $this->assertTrue($cacheItem->isHit());

        $rawSpace = guzzle_json_decode($cacheItem->get(), true);
        $this->assertSame('cfexampleapi', $rawSpace['sys']['id']);

        $clearer->clear();
        $this->assertFalse(self::$cache->hasItem(
            $resourcePool->generateKey('Space', 'cfexampleapi')
        ));

        self::$cache->clear();
    }

    /**
     * @vcr cache_api_works_with_empty_cache.json
     */
    public function testApiWorksWithEmptyCache()
    {
        self::$cache->clear();

        $client = $this->getClient('default_cache');

        $this->assertSame('cfexampleapi', $client->getSpace()->getId());
        $this->assertSame('cat', $client->getContentType('cat')->getId());

        self::$cache->clear();
    }

    /**
     * @vcr cache_access_cached_content.json
     */
    public function testAccessCachedContent()
    {
        self::$cache->clear();

        $client = $this->getClient('default');

        $warmer = new CacheWarmer($client, $client->getResourcePool(), self::$cache);
        $warmer->warmUp();

        $client = $this->getClient('default_cache');

        $this->assertSame('cfexampleapi', $client->getSpace()->getId());
        $this->assertSame('cat', $client->getContentType('cat')->getId());

        self::$cache->clear();
    }

    /**
     * @vcr cache_cached_content_auto_warmup.json
     */
    public function testCachedContentAutoWarmup()
    {
        self::$cache->clear();

        $client = $this->getClient('default_cache_autowarmup');
        $resourcePool = $client->getResourcePool();

        $this->assertSame('cfexampleapi', $client->getSpace()->getId());
        $this->assertSame('cat', $client->getContentType('cat')->getId());

        $cacheItem = self::$cache->getItem($resourcePool->generateKey('Space', 'cfexampleapi'));
        $this->assertTrue($cacheItem->isHit());

        $resource = guzzle_json_decode($cacheItem->get(), true);
        $this->assertSame('cfexampleapi', $resource['sys']['id']);

        self::$cache->clear();
    }

    /**
     * @vcr cache_cached_content_auto_warmup_with_entries_and_assets.json
     */
    public function testCachedContentAutoWarmupWithEntriesAndAssets()
    {
        self::$cache->clear();

        $client = $this->getClient('default_cache_autowarmup_content');
        $resourcePool = $client->getResourcePool();

        $this->assertSame('cfexampleapi', $client->getSpace()->getId());
        $this->assertSame('cat', $client->getContentType('cat')->getId());
        $this->assertSame('nyancat', $client->getEntry('nyancat', '*')->getId());
        $this->assertSame('nyancat', $client->getAsset('nyancat', '*')->getId());

        $cacheItem = self::$cache->getItem($resourcePool->generateKey('Space', 'cfexampleapi'));
        $this->assertTrue($cacheItem->isHit());
        $resource = guzzle_json_decode($cacheItem->get(), true);
        $this->assertSame('cfexampleapi', $resource['sys']['id']);
        $this->assertSame('Space', $resource['sys']['type']);

        $cacheItem = self::$cache->getItem($resourcePool->generateKey('ContentType', 'cat'));
        $this->assertTrue($cacheItem->isHit());
        $resource = guzzle_json_decode($cacheItem->get(), true);
        $this->assertSame('cat', $resource['sys']['id']);
        $this->assertSame('ContentType', $resource['sys']['type']);

        $cacheItem = self::$cache->getItem($resourcePool->generateKey('Entry', 'nyancat'));
        $this->assertTrue($cacheItem->isHit());
        $resource = guzzle_json_decode($cacheItem->get(), true);
        $this->assertSame('nyancat', $resource['sys']['id']);
        $this->assertSame('Entry', $resource['sys']['type']);

        $cacheItem = self::$cache->getItem($resourcePool->generateKey('Asset', 'nyancat'));
        $this->assertTrue($cacheItem->isHit());
        $resource = guzzle_json_decode($cacheItem->get(), true);
        $this->assertSame('nyancat', $resource['sys']['id']);
        $this->assertSame('Asset', $resource['sys']['type']);

        self::$cache->clear();
    }

    /**
     * @vcr cache_invalid_cached_content_type.json
     */
    public function testInvalidCachedContentType()
    {
        self::$cache->clear();

        $client = $this->getClient('new');

        // This fake content type does not contain fields
        // which will actually be in the real API request.
        $client->parseJson($this->getFixtureContent('invalid_content_type.json'));

        $errorFields = ['name', 'jobTitle', 'picture'];
        // When building entries, missing fields are supposed to trigger
        // a silenced error message for every missing field.
        set_error_handler(function ($errorCode, $errorMessage) use (&$errorFields) {
            $field = array_shift($errorFields);

            $this->assertSame(
                'Entry of content type "Person" ("person") being built contains field "'.$field.'" which is not present in the content type definition. Please check your cache for stale content type definitions.',
                $errorMessage
            );
            $this->assertSame(512, $errorCode);
        }, \E_USER_WARNING);

        $entry = $client->getEntry('Kpwt1njxgAm04oQYyUScm');
        restore_error_handler();

        $this->assertSame('Ben Chang', $entry->getName());
        $this->assertSame('Señor', $entry->getJobTitle());
        $this->assertSame([
            'sys' => [
                'type' => 'Link',
                'linkType' => 'Asset',
                'id' => 'SQOIQ1rZMQQUeyoyGiEUq',
            ],
        ], $entry->getPicture());
    }

    /**
     * @vcr cache_queries.json
     */
    public function testGetEntriesIsCached()
    {
        self::$cache->clear();

        $client = $this->getClient('default');
        $queryPool = $client->getQueryPool();

        $query = new Query();
        $query->setContentType('cat');

        $this->assertFalse($queryPool->has($query));

        $entries = $client->getEntries($query);

        $this->assertTrue($queryPool->has($query));

        $cachedEntries = $queryPool->get($query);
        $this->assertEquals($entries, $cachedEntries);
    }

    /**
     * @vcr cache_queries.json
     */
    public function testGetEntriesIsNotCachedAcrossClients()
    {
        self::$cache->clear();

        $firstClient = $this->getClient('default');
        $firstQueryPool = $firstClient->getQueryPool();

        $query = new Query();
        $query->setContentType('cat');

        $this->assertFalse($firstQueryPool->has($query));

        $entries = $firstClient->getEntries($query);

        $this->assertTrue($firstQueryPool->has($query));

        $cachedEntries = $firstQueryPool->get($query);
        $this->assertEquals($entries, $cachedEntries);

        $secondClient = $this->getClient('default');
        $secondQueryPool = $secondClient->getQueryPool();

        $this->assertFalse($secondQueryPool->has($query));
    }

    /**
     * @vcr cache_queries.json
     */
    public function testGetEntriesIsCachedAcrossClientsWithQueryCache()
    {
        self::$cache->clear();

        $query = new Query();
        $query->setContentType('cat');

        $firstClient = $this->getClient('default_cache_query');
        $firstQueryPool = $firstClient->getQueryPool();
        $this->assertFalse($firstQueryPool->has($query));

        $firstClientEntries = $firstClient->getEntries($query);

        $this->assertTrue($firstQueryPool->has($query));
        $firstClientCachedEntries = $firstQueryPool->get($query);
        $this->assertEquals($firstClientEntries, $firstClientCachedEntries);

        $secondClient = $this->getClient('default_cache_query');
        $secondQueryPool = $secondClient->getQueryPool();

        $this->assertTrue($secondQueryPool->has($query));

        $secondClientCachedEntries = $secondQueryPool->get($query);
        $this->assertEquals($secondClient->getEntries($query), $secondClientCachedEntries);

        foreach ($firstClientCachedEntries->getItems() as $i => $firstClientCachedEntry) {
            $this->assertEquals($firstClientCachedEntry->getId(), $secondClientCachedEntries[$i]->getId());
        }
    }

    /**
     * @vcr cache_queries.json
     */
    public function testGetEntriesCacheExpiresAfterLifetime()
    {
        self::$cache->clear();

        $client = $this->getClient('2_seconds_lifetime_cache_query');
        $queryPool = $client->getQueryPool();

        $query = new Query();
        $query->setContentType('cat');

        $this->assertFalse($queryPool->has($query));

        $client->getEntries($query);

        $this->assertTrue($queryPool->has($query));

        sleep(2);

        $client = $this->getClient('2_seconds_lifetime_cache_query');
        $queryPool = $client->getQueryPool();

        $this->assertFalse($queryPool->has($query));
    }
}

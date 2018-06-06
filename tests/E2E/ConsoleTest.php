<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery\E2E;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Contentful\Delivery\Cache\CacheItemPoolFactoryInterface;
use Contentful\Delivery\Console\Application;
use Contentful\Tests\Delivery\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Tester\CommandTester;

class ConsoleTest extends TestCase
{
    /**
     * @param string $commandName
     * @param array  $params
     *
     * @return string
     */
    private function getConsoleOutput($commandName, array $params)
    {
        $application = new Application();
        $command = $application->find($commandName);

        $tester = new CommandTester($command);
        $tester->execute(\array_merge(['command' => $command->getName()], $params));

        return $tester->getDisplay();
    }

    /**
     * @vcr e2e_console_cache_warmup_delivery.json
     */
    public function testCacheWarmupDelivery()
    {
        $output = $this->getConsoleOutput('delivery:cache:warmup', [
            '--access-token' => 'b4c0n73n7fu1',
            '--space-id' => 'cfexampleapi',
            '--environment-id' => 'master',
            '--factory-class' => CacheItemPoolFactory::class,
        ]);

        $this->assertContains('Cache warmed up for space "cfexampleapi" on environment "master" using API "DELIVERY".', $output);

        $cachePool = CacheItemPoolFactory::$pools['DELIVERY.cfexampleapi.master'];

        $this->assertTrue($cachePool->hasItem('contentful.DELIVERY.cfexampleapi.master.Space.cfexampleapi'));
        $this->assertTrue($cachePool->hasItem('contentful.DELIVERY.cfexampleapi.master.Environment.master'));
        $this->assertTrue($cachePool->hasItem('contentful.DELIVERY.cfexampleapi.master.ContentType.cat'));
        $this->assertTrue($cachePool->hasItem('contentful.DELIVERY.cfexampleapi.master.ContentType.dog'));
        $this->assertTrue($cachePool->hasItem('contentful.DELIVERY.cfexampleapi.master.ContentType.human'));
    }

    /**
     * @vcr e2e_console_cache_warmup_preview.json
     */
    public function testCacheWarmupPreview()
    {
        $output = $this->getConsoleOutput('delivery:cache:warmup', [
            '--access-token' => 'e5e8d4c5c122cf28fc1af3ff77d28bef78a3952957f15067bbc29f2f0dde0b50',
            '--space-id' => 'cfexampleapi',
            '--environment-id' => 'master',
            '--factory-class' => CacheItemPoolFactory::class,
            '--use-preview' => true,
        ]);

        $this->assertContains('Cache warmed up for space "cfexampleapi" on environment "master" using API "PREVIEW".', $output);

        $cachePool = CacheItemPoolFactory::$pools['PREVIEW.cfexampleapi.master'];

        $this->assertTrue($cachePool->hasItem('contentful.PREVIEW.cfexampleapi.master.Space.cfexampleapi'));
        $this->assertTrue($cachePool->hasItem('contentful.PREVIEW.cfexampleapi.master.Environment.master'));
        $this->assertTrue($cachePool->hasItem('contentful.PREVIEW.cfexampleapi.master.ContentType.cat'));
        $this->assertTrue($cachePool->hasItem('contentful.PREVIEW.cfexampleapi.master.ContentType.dog'));
        $this->assertTrue($cachePool->hasItem('contentful.PREVIEW.cfexampleapi.master.ContentType.human'));
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Object returned by "Contentful\Tests\Delivery\E2E\InvalidFactory::getCacheItemPool()" must be PSR-6 compatible and implement "Psr\Cache\CacheItemPoolInterface".
     */
    public function testCacheWarmupInvalidFactoryReturn()
    {
        $this->getConsoleOutput('delivery:cache:warmup', [
            '--access-token' => 'b4c0n73n7fu1',
            '--space-id' => 'cfexampleapi',
            '--environment-id' => 'master',
            '--factory-class' => InvalidFactory::class,
        ]);
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Cache item pool factory must implement "Contentful\Delivery\Cache\CacheItemPoolFactoryInterface".
     */
    public function testCacheWarmupInvalidFactory()
    {
        $this->getConsoleOutput('delivery:cache:warmup', [
            '--access-token' => 'b4c0n73n7fu1',
            '--space-id' => 'cfexampleapi',
            '--environment-id' => 'master',
            '--factory-class' => \stdClass::class,
        ]);
    }

    /**
     * @vcr e2e_console_cache_warmup_not_working.json
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage The SDK could not warm up the cache. Try checking your PSR-6 implementation (class "Contentful\Tests\Delivery\E2E\NotWorkingCachePool").
     */
    public function testCacheWarmupNotWorking()
    {
        $this->getConsoleOutput('delivery:cache:warmup', [
            '--access-token' => 'b4c0n73n7fu1',
            '--space-id' => 'cfexampleapi',
            '--environment-id' => 'master',
            '--factory-class' => NotWorkingCachePoolFactory::class,
        ]);
    }

    /**
     * @vcr e2e_console_cache_clear_delivery.json
     */
    public function testCacheClearDelivery()
    {
        $output = $this->getConsoleOutput('delivery:cache:clear', [
            '--access-token' => 'b4c0n73n7fu1',
            '--space-id' => 'cfexampleapi',
            '--environment-id' => 'master',
            '--factory-class' => CacheItemPoolFactory::class,
        ]);

        $this->assertContains('Cache cleared for space "cfexampleapi" on environment "master" using API "DELIVERY".', $output);

        $cachePool = CacheItemPoolFactory::$pools['DELIVERY.cfexampleapi.master'];
        $this->assertFalse($cachePool->hasItem('contentful.DELIVERY.cfexampleapi.master.Space.cfexampleapi'));
    }

    /**
     * @vcr e2e_console_cache_clear_preview.json
     */
    public function testCacheClearPreview()
    {
        $output = $this->getConsoleOutput('delivery:cache:clear', [
            '--access-token' => 'e5e8d4c5c122cf28fc1af3ff77d28bef78a3952957f15067bbc29f2f0dde0b50',
            '--space-id' => 'cfexampleapi',
            '--environment-id' => 'master',
            '--factory-class' => CacheItemPoolFactory::class,
            '--use-preview' => true,
        ]);

        $this->assertContains('Cache cleared for space "cfexampleapi" on environment "master" using API "PREVIEW".', $output);

        $cachePool = CacheItemPoolFactory::$pools['PREVIEW.cfexampleapi.master'];
        $this->assertFalse($cachePool->hasItem('contentful.PREVIEW.cfexampleapi.master.Space.cfexampleapi'));
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Object returned by "Contentful\Tests\Delivery\E2E\InvalidFactory::getCacheItemPool()" must be PSR-6 compatible and implement "Psr\Cache\CacheItemPoolInterface".
     */
    public function testCacheClearInvalidFactoryReturn()
    {
        $this->getConsoleOutput('delivery:cache:clear', [
            '--access-token' => 'b4c0n73n7fu1',
            '--space-id' => 'cfexampleapi',
            '--environment-id' => 'master',
            '--factory-class' => InvalidFactory::class,
        ]);
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Cache item pool factory must implement "Contentful\Delivery\Cache\CacheItemPoolFactoryInterface".
     */
    public function testCacheClearInvalidFactory()
    {
        $this->getConsoleOutput('delivery:cache:clear', [
            '--access-token' => 'b4c0n73n7fu1',
            '--space-id' => 'cfexampleapi',
            '--environment-id' => 'master',
            '--factory-class' => \stdClass::class,
        ]);
    }

    /**
     * @vcr e2e_console_cache_clear_not_working.json
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage The SDK could not clear the cache. Try checking your PSR-6 implementation (class "Contentful\Tests\Delivery\E2E\NotWorkingCachePool").
     */
    public function testCacheClearNotWorking()
    {
        $this->getConsoleOutput('delivery:cache:clear', [
            '--access-token' => 'b4c0n73n7fu1',
            '--space-id' => 'cfexampleapi',
            '--environment-id' => 'master',
            '--factory-class' => NotWorkingCachePoolFactory::class,
        ]);
    }
}

class CacheItemPoolFactory implements CacheItemPoolFactoryInterface
{
    /**
     * @var ArrayCachePool[]
     */
    public static $pools = [];

    public function __construct()
    {
        self::$pools = [];
    }

    public function getCacheItemPool($api, $spaceId, $environmentId)
    {
        $key = $api.'.'.$spaceId.'.'.$environmentId;
        if (!isset(self::$pools[$key])) {
            self::$pools[$key] = new ArrayCachePool();
        }

        return self::$pools[$key];
    }
}

class InvalidFactory implements CacheItemPoolFactoryInterface
{
    public function __construct()
    {
    }

    public function getCacheItemPool($api, $spaceId, $environmentId)
    {
        return null;
    }
}

class NotWorkingCachePoolFactory implements CacheItemPoolFactoryInterface
{
    public function __construct()
    {
    }

    public function getCacheItemPool($api, $spaceId, $environmentId)
    {
        return new NotWorkingCachePool();
    }
}

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
        return false;
    }

    public function clear()
    {
        return false;
    }

    public function deleteItem($key)
    {
        return false;
    }

    public function deleteItems(array $keys)
    {
        return false;
    }

    public function save(CacheItemInterface $item)
    {
        return false;
    }

    public function saveDeferred(CacheItemInterface $item)
    {
        return false;
    }

    public function commit()
    {
        return false;
    }
}

class NotWorkingCacheItem implements CacheItemInterface
{
    private $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function get()
    {
        return null;
    }

    public function isHit()
    {
        return false;
    }

    public function set($value)
    {
        return $this;
    }

    public function expiresAt($expiration)
    {
        return $this;
    }

    public function expiresAfter($time)
    {
        return $this;
    }
}

<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\E2E;

use Contentful\Delivery\Console\Application;
use Contentful\Tests\Delivery\Implementation\CacheItemPoolFactory;
use Contentful\Tests\Delivery\Implementation\NotWorkingCachePoolFactory;
use Contentful\Tests\Delivery\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ConsoleTest extends TestCase
{
    private function getConsoleOutput(string $commandName, array $params): string
    {
        $application = new Application();
        $command = $application->find($commandName);

        $tester = new CommandTester($command);
        $tester->execute(array_merge(['command' => $command->getName()], $params));

        return $tester->getDisplay();
    }

    /**
     * @vcr console_cache_warmup_delivery.json
     */
    public function testCacheWarmupDelivery()
    {
        $output = $this->getConsoleOutput('delivery:cache:warmup', [
            '--access-token' => 'b4c0n73n7fu1',
            '--space-id' => 'cfexampleapi',
            '--environment-id' => 'master',
            '--factory-class' => CacheItemPoolFactory::class,
        ]);

        $this->assertStringContainsStringIgnoringCase('Cache warmed up for space "cfexampleapi" on environment "master" using API "DELIVERY".', $output);

        $cachePool = CacheItemPoolFactory::$pools['DELIVERY.cfexampleapi.master'];

        $this->assertTrue($cachePool->hasItem('contentful.DELIVERY.cfexampleapi.master.Space.cfexampleapi.__ALL__'));
        $this->assertTrue($cachePool->hasItem('contentful.DELIVERY.cfexampleapi.master.Environment.master.__ALL__'));
        $this->assertTrue($cachePool->hasItem('contentful.DELIVERY.cfexampleapi.master.ContentType.cat.__ALL__'));
        $this->assertTrue($cachePool->hasItem('contentful.DELIVERY.cfexampleapi.master.ContentType.dog.__ALL__'));
        $this->assertTrue($cachePool->hasItem('contentful.DELIVERY.cfexampleapi.master.ContentType.human.__ALL__'));
    }

    /**
     * @vcr console_cache_warmup_preview.json
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

        $this->assertStringContainsStringIgnoringCase('Cache warmed up for space "cfexampleapi" on environment "master" using API "PREVIEW".', $output);

        $cachePool = CacheItemPoolFactory::$pools['PREVIEW.cfexampleapi.master'];

        $this->assertTrue($cachePool->hasItem('contentful.PREVIEW.cfexampleapi.master.Space.cfexampleapi.__ALL__'));
        $this->assertTrue($cachePool->hasItem('contentful.PREVIEW.cfexampleapi.master.Environment.master.__ALL__'));
        $this->assertTrue($cachePool->hasItem('contentful.PREVIEW.cfexampleapi.master.ContentType.cat.__ALL__'));
        $this->assertTrue($cachePool->hasItem('contentful.PREVIEW.cfexampleapi.master.ContentType.dog.__ALL__'));
        $this->assertTrue($cachePool->hasItem('contentful.PREVIEW.cfexampleapi.master.ContentType.human.__ALL__'));
    }

    public function testCacheWarmupInvalidFactory()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Cache item pool factory must implement \"Contentful\Delivery\Cache\CacheItemPoolFactoryInterface\".");

        $this->getConsoleOutput('delivery:cache:warmup', [
            '--access-token' => 'b4c0n73n7fu1',
            '--space-id' => 'cfexampleapi',
            '--environment-id' => 'master',
            '--factory-class' => \stdClass::class,
        ]);
    }

    /**
     * @vcr console_cache_warmup_not_working.json
     */
    public function testCacheWarmupNotWorking()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("The SDK could not warm up the cache. Try checking your PSR-6 implementation (class \"Contentful\Tests\Delivery\Implementation\NotWorkingCachePool\").");

        $this->getConsoleOutput('delivery:cache:warmup', [
            '--access-token' => 'b4c0n73n7fu1',
            '--space-id' => 'cfexampleapi',
            '--environment-id' => 'master',
            '--factory-class' => NotWorkingCachePoolFactory::class,
        ]);
    }

    /**
     * @vcr console_cache_clear_delivery.json
     */
    public function testCacheClearDelivery()
    {
        $output = $this->getConsoleOutput('delivery:cache:clear', [
            '--access-token' => 'b4c0n73n7fu1',
            '--space-id' => 'cfexampleapi',
            '--environment-id' => 'master',
            '--factory-class' => CacheItemPoolFactory::class,
        ]);

        $this->assertStringContainsStringIgnoringCase('Cache cleared for space "cfexampleapi" on environment "master" using API "DELIVERY".', $output);

        $cachePool = CacheItemPoolFactory::$pools['DELIVERY.cfexampleapi.master'];
        $this->assertFalse($cachePool->hasItem('contentful.DELIVERY.cfexampleapi.master.Space.cfexampleapi.__ALL__'));
    }

    /**
     * @vcr console_cache_clear_preview.json
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

        $this->assertStringContainsStringIgnoringCase('Cache cleared for space "cfexampleapi" on environment "master" using API "PREVIEW".', $output);

        $cachePool = CacheItemPoolFactory::$pools['PREVIEW.cfexampleapi.master'];
        $this->assertFalse($cachePool->hasItem('contentful.PREVIEW.cfexampleapi.master.Space.cfexampleapi.__ALL__'));
    }

    public function testCacheClearInvalidFactory()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Cache item pool factory must implement \"Contentful\Delivery\Cache\CacheItemPoolFactoryInterface\".");

        $this->getConsoleOutput('delivery:cache:clear', [
            '--access-token' => 'b4c0n73n7fu1',
            '--space-id' => 'cfexampleapi',
            '--environment-id' => 'master',
            '--factory-class' => \stdClass::class,
        ]);
    }

    /**
     * @vcr console_cache_clear_not_working.json
     */
    public function testCacheClearNotWorking()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("The SDK could not clear the cache. Try checking your PSR-6 implementation (class \"Contentful\Tests\Delivery\Implementation\NotWorkingCachePool\").");

        $this->getConsoleOutput('delivery:cache:clear', [
            '--access-token' => 'b4c0n73n7fu1',
            '--space-id' => 'cfexampleapi',
            '--environment-id' => 'master',
            '--factory-class' => NotWorkingCachePoolFactory::class,
        ]);
    }

    /**
     * @vcr console_cache_warmup_with_content.json
     */
    public function testCacheWarmupWithContent()
    {
        $output = $this->getConsoleOutput('delivery:cache:warmup', [
            '--access-token' => 'b4c0n73n7fu1',
            '--space-id' => 'cfexampleapi',
            '--environment-id' => 'master',
            '--factory-class' => CacheItemPoolFactory::class,
            '--cache-content' => true,
        ]);

        $this->assertStringContainsStringIgnoringCase('Cache warmed up for space "cfexampleapi" on environment "master" using API "DELIVERY".', $output);

        $cachePool = CacheItemPoolFactory::$pools['DELIVERY.cfexampleapi.master'];

        $this->assertTrue($cachePool->hasItem('contentful.DELIVERY.cfexampleapi.master.Space.cfexampleapi.__ALL__'));
        $this->assertTrue($cachePool->hasItem('contentful.DELIVERY.cfexampleapi.master.Environment.master.__ALL__'));
        $this->assertTrue($cachePool->hasItem('contentful.DELIVERY.cfexampleapi.master.ContentType.cat.__ALL__'));
        $this->assertTrue($cachePool->hasItem('contentful.DELIVERY.cfexampleapi.master.ContentType.dog.__ALL__'));
        $this->assertTrue($cachePool->hasItem('contentful.DELIVERY.cfexampleapi.master.ContentType.human.__ALL__'));

        $this->assertTrue($cachePool->hasItem('contentful.DELIVERY.cfexampleapi.master.Entry.nyancat.__ALL__'));
        $this->assertTrue($cachePool->hasItem('contentful.DELIVERY.cfexampleapi.master.Entry.nyancat.en_US'));
        $this->assertTrue($cachePool->hasItem('contentful.DELIVERY.cfexampleapi.master.Entry.nyancat.tlh'));
    }

    /**
     * @vcr console_cache_clear_with_content.json
     */
    public function testCacheClearWithContent()
    {
        $output = $this->getConsoleOutput('delivery:cache:clear', [
            '--access-token' => 'b4c0n73n7fu1',
            '--space-id' => 'cfexampleapi',
            '--environment-id' => 'master',
            '--factory-class' => CacheItemPoolFactory::class,
            '--cache-content' => true,
        ]);

        $this->assertStringContainsStringIgnoringCase('Cache cleared for space "cfexampleapi" on environment "master" using API "DELIVERY".', $output);

        $cachePool = CacheItemPoolFactory::$pools['DELIVERY.cfexampleapi.master'];
        $this->assertFalse($cachePool->hasItem('contentful.DELIVERY.cfexampleapi.master.Space.cfexampleapi.__ALL__'));

        $this->assertFalse($cachePool->hasItem('contentful.DELIVERY.cfexampleapi.master.Entry.nyancat.__ALL__'));
    }
}

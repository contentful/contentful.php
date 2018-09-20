<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Unit;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Contentful\Core\Log\NullLogger;
use Contentful\Delivery\ClientOptions;
use Contentful\Tests\Delivery\TestCase;
use GuzzleHttp\Client as HttpClient;
use Psr\Log\LoggerInterface;

class ClientOptionsTest extends TestCase
{
    public function testDefaultValues()
    {
        $options = ClientOptions::create();
        $this->assertInstanceOf(ClientOptions::class, $options);

        $this->assertSame('https://cdn.contentful.com', $options->getHost());
        $this->assertInstanceOf(LoggerInterface::class, $options->getLogger());
        $this->assertInstanceOf(HttpClient::class, $options->getHttpClient());
        $this->assertFalse($options->hasCacheAutoWarmup());
        $this->assertFalse($options->hasCacheContent());
        $this->assertNull($options->getDefaultLocale());
    }

    public function testImmutable()
    {
        $options = new ClientOptions();

        $this->assertNotSame($options, $options->usingDeliveryApi());
        $this->assertNotSame($options, $options->usingPreviewApi());
        $this->assertNotSame($options, $options->withHost('https://cdn.contentful.com'));
        $this->assertNotSame($options, $options->withDefaultLocale('en-US'));
        $this->assertNotSame($options, $options->withCache(new ArrayCachePool(), \false, \false));
        $this->assertNotSame($options, $options->withLogger(new NullLogger()));
        $this->assertNotSame($options, $options->withHttpClient(new HttpClient()));
    }

    public function testGetSet()
    {
        $options = new ClientOptions();

        $options = $options->usingPreviewApi();
        $this->assertSame('https://preview.contentful.com', $options->getHost());

        $options = $options->withHost('https://www.example.com/');
        $this->assertSame('https://www.example.com', $options->getHost());

        $options = $options->withDefaultLocale('it-IT');
        $this->assertSame('it-IT', $options->getDefaultLocale());

        $cachePool = new ArrayCachePool();
        $options = $options->withCache($cachePool, \true, \true);
        $this->assertSame($cachePool, $options->getCacheItemPool());
        $this->assertTrue($options->hasCacheAutoWarmup());
        $this->assertTrue($options->hasCacheContent());

        $logger = new NullLogger();
        $options = $options->withLogger($logger);
        $this->assertSame($logger, $options->getLogger());

        $client = new HttpClient();
        $options = $options->withHttpClient($client);
        $this->assertSame($client, $options->getHttpClient());
    }
}

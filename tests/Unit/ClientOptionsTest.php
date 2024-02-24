<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Unit;

use Contentful\Delivery\ClientOptions;
use Contentful\Tests\Delivery\TestCase;
use GuzzleHttp\Client as HttpClient;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

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
        $this->assertFalse($options->usesLowMemoryResourcePool());
        $this->assertTrue($options->usesMessageLogging());
    }

    public function testGetSet()
    {
        $options = new ClientOptions();

        $options->usingPreviewApi();
        $this->assertSame('https://preview.contentful.com', $options->getHost());

        $options->usingDeliveryApi();
        $this->assertSame('https://cdn.contentful.com', $options->getHost());

        $options->withHost('https://www.example.com/');
        $this->assertSame('https://www.example.com', $options->getHost());

        $options->withDefaultLocale('it-IT');
        $this->assertSame('it-IT', $options->getDefaultLocale());

        $cachePool = new ArrayAdapter();
        $options->withCache($cachePool, true, true);
        $this->assertSame($cachePool, $options->getCacheItemPool());
        $this->assertTrue($options->hasCacheAutoWarmup());
        $this->assertTrue($options->hasCacheContent());

        $logger = new NullLogger();
        $options->withLogger($logger);
        $this->assertSame($logger, $options->getLogger());

        $client = new HttpClient();
        $options->withHttpClient($client);
        $this->assertSame($client, $options->getHttpClient());

        $options->withLowMemoryResourcePool();
        $this->assertTrue($options->usesLowMemoryResourcePool());

        $options->withNormalResourcePool();
        $this->assertFalse($options->usesLowMemoryResourcePool());

        $options->withoutMessageLogging();
        $this->assertFalse($options->usesMessageLogging());
    }
}

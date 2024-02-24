<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery;

use GuzzleHttp\Client as HttpClient;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\NullAdapter;

class ClientOptions
{
    /**
     * @var string
     */
    private $host = Client::URI_DELIVERY;

    /**
     * @var CacheItemPoolInterface
     */
    private $cacheItemPool;

    /**
     * @var bool
     */
    private $cacheAutoWarmup = false;

    /**
     * @var bool
     */
    private $cacheContent = false;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string|null
     */
    private $defaultLocale;

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var bool
     */
    private $usesLowMemoryResourcePool = false;

    /**
     * @var bool
     */
    private $messageLogging = true;

    /**
     * @var CacheItemPoolInterface
     */
    private $queryCacheItemPool;

    /**
     * @var int
     */
    private $queryCacheLifetime = 0;

    /**
     * ClientOptions constructor.
     */
    public function __construct()
    {
        $this->cacheItemPool = new NullAdapter();
        $this->logger = new NullLogger();
        $this->httpClient = new HttpClient();
        $this->queryCacheItemPool = new NullAdapter();
    }

    public static function create(): self
    {
        return new self();
    }

    /**
     * @return self
     */
    public function withDefaultLocale(string $locale)
    {
        $this->defaultLocale = $locale;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    public function usingDeliveryApi(): self
    {
        $this->host = Client::URI_DELIVERY;

        return $this;
    }

    public function usingPreviewApi(): self
    {
        $this->host = Client::URI_PREVIEW;

        return $this;
    }

    public function withHost(string $host): self
    {
        if ('/' === mb_substr($host, -1)) {
            $host = mb_substr($host, 0, -1);
        }

        $this->host = $host;

        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function withCache(
        CacheItemPoolInterface $cacheItemPool,
        bool $autoWarmup = false,
        bool $cacheContent = false
    ): self {
        $this->cacheItemPool = $cacheItemPool;
        $this->cacheAutoWarmup = $autoWarmup;
        $this->cacheContent = $cacheContent;

        return $this;
    }

    public function getCacheItemPool(): CacheItemPoolInterface
    {
        return $this->cacheItemPool;
    }

    public function hasCacheAutoWarmup(): bool
    {
        return $this->cacheAutoWarmup;
    }

    public function hasCacheContent(): bool
    {
        return $this->cacheContent;
    }

    /**
     * Configure the Client to use any PSR-3 compatible logger.
     */
    public function withLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function withHttpClient(HttpClient $client): self
    {
        $this->httpClient = $client;

        return $this;
    }

    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }

    /**
     * Configures the client to use the default resource pool implementation,
     * which may use more memory in extreme scenarios (tens of thousands of resources).
     */
    public function withNormalResourcePool(): self
    {
        $this->usesLowMemoryResourcePool = false;

        return $this;
    }

    /**
     * Configures the client to use a resource pool which will not cache entries and assets,
     * which is useful when handling tens of thousand of resources,
     * but it may cause extra API calls in normal scenarios.
     * Use this option only if the default resource pool is causing you memory errors.
     */
    public function withLowMemoryResourcePool(): self
    {
        $this->usesLowMemoryResourcePool = true;

        return $this;
    }

    public function usesLowMemoryResourcePool(): bool
    {
        return $this->usesLowMemoryResourcePool;
    }

    public function withoutMessageLogging(): self
    {
        $this->messageLogging = false;

        return $this;
    }

    public function usesMessageLogging(): bool
    {
        return $this->messageLogging;
    }

    public function withQueryCache(CacheItemPoolInterface $queryCacheItemPool, int $queryCacheLifetime = 0): self
    {
        $this->queryCacheItemPool = $queryCacheItemPool;
        $this->queryCacheLifetime = $queryCacheLifetime;

        return $this;
    }

    public function getQueryCacheItemPool(): CacheItemPoolInterface
    {
        return $this->queryCacheItemPool;
    }

    public function getQueryCacheLifetime(): int
    {
        return $this->queryCacheLifetime;
    }
}

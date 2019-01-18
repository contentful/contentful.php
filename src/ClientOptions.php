<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2019 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery;

use Cache\Adapter\Void\VoidCachePool;
use Contentful\Core\Log\NullLogger;
use GuzzleHttp\Client as HttpClient;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

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
    private $cacheAutoWarmup = \false;

    /**
     * @var bool
     */
    private $cacheContent = \false;

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
    private $usesLowMemoryResourcePool = \false;

    /**
     * ClientOptions constructor.
     */
    public function __construct()
    {
        $this->cacheItemPool = new VoidCachePool();
        $this->logger = new NullLogger();
        $this->httpClient = new HttpClient();
    }

    /**
     * @return self
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * @param string $locale
     *
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

    /**
     * @return self
     */
    public function usingDeliveryApi(): self
    {
        $this->host = Client::URI_DELIVERY;

        return $this;
    }

    /**
     * @return self
     */
    public function usingPreviewApi(): self
    {
        $this->host = Client::URI_PREVIEW;

        return $this;
    }

    /**
     * @param string $host
     *
     * @return self
     */
    public function withHost(string $host): self
    {
        if ('/' === \mb_substr($host, -1)) {
            $host = \mb_substr($host, 0, -1);
        }

        $this->host = $host;

        return $this;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param CacheItemPoolInterface $cacheItemPool
     * @param bool                   $autoWarmup
     * @param bool                   $cacheContent
     *
     * @return self
     */
    public function withCache(
        CacheItemPoolInterface $cacheItemPool,
        bool $autoWarmup = \false,
        bool $cacheContent = \false
    ): self {
        $this->cacheItemPool = $cacheItemPool;
        $this->cacheAutoWarmup = $autoWarmup;
        $this->cacheContent = $cacheContent;

        return $this;
    }

    /**
     * @return CacheItemPoolInterface
     */
    public function getCacheItemPool(): CacheItemPoolInterface
    {
        return $this->cacheItemPool;
    }

    /**
     * @return bool
     */
    public function hasCacheAutoWarmup(): bool
    {
        return $this->cacheAutoWarmup;
    }

    /**
     * @return bool
     */
    public function hasCacheContent(): bool
    {
        return $this->cacheContent;
    }

    /**
     * Configure the Client to use any PSR-3 compatible logger.
     *
     * @param LoggerInterface $logger
     *
     * @return self
     */
    public function withLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param HttpClient $client
     *
     * @return self
     */
    public function withHttpClient(HttpClient $client): self
    {
        $this->httpClient = $client;

        return $this;
    }

    /**
     * @return HttpClient
     */
    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }

    /**
     * Configures the client to use the default resource pool implementation,
     * which may use more memory in extreme scenarios (tens of thousands of resources).
     *
     * @return ClientOptions
     */
    public function withNormalResourcePool(): self
    {
        $this->usesLowMemoryResourcePool = \false;

        return $this;
    }

    /**
     * Configures the client to use a resource pool which will not cache entries and assets,
     * which is useful when handling tens of thousand of resources,
     * but it may cause extra API calls in normal scenarios.
     * Use this option only if the default resource pool is causing you memory errors.
     *
     * @return ClientOptions
     */
    public function withLowMemoryResourcePool(): self
    {
        $this->usesLowMemoryResourcePool = \true;

        return $this;
    }

    /**
     * @return bool
     */
    public function usesLowMemoryResourcePool(): bool
    {
        return $this->usesLowMemoryResourcePool;
    }
}

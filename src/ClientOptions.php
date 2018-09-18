<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
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
        $clone = clone $this;
        $clone->defaultLocale = $locale;

        return $clone;
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
        $clone = clone $this;
        $clone->host = Client::URI_DELIVERY;

        return $clone;
    }

    /**
     * @return self
     */
    public function usingPreviewApi(): self
    {
        $clone = clone $this;
        $clone->host = Client::URI_PREVIEW;

        return $clone;
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

        $clone = clone $this;
        $clone->host = $host;

        return $clone;
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
        $clone = clone $this;
        $clone->cacheItemPool = $cacheItemPool;
        $clone->cacheAutoWarmup = $autoWarmup;
        $clone->cacheContent = $cacheContent;

        return $clone;
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
        $clone = clone $this;
        $clone->logger = $logger;

        return $clone;
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
        $clone = clone $this;
        $clone->httpClient = $client;

        return $clone;
    }

    /**
     * @return HttpClient
     */
    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }
}
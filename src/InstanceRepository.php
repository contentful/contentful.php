<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery;

use Contentful\Core\Resource\ResourceInterface;
use Contentful\Core\Resource\SystemPropertiesInterface;
use Contentful\Delivery\SystemProperties\LocalizedResource as LocalizedResourceSystemProperties;
use Psr\Cache\CacheItemPoolInterface;
use function GuzzleHttp\json_encode as guzzle_json_encode;

/**
 * InstanceRepository class.
 *
 * This class acts as a registry for current objects managed by the Client.
 * It also abstracts access to objects stored in cache.
 */
class InstanceRepository
{
    /**
     * @var ResourceInterface[]
     */
    private $resources = [];

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $api;

    /**
     * @var CacheItemPoolInterface
     */
    private $cacheItemPool;

    /**
     * When retrieving from cache, there's the risk of an endless loop,
     * so we keep track of the resources we're currently warming up, and
     * skip the process if we track that we're already in the process of doing so.
     *
     * @var bool[]
     */
    private $warmupStack = [];

    /**
     * @var string
     */
    private $spaceId;

    /**
     * @var string
     */
    private $environmentId;

    /**
     * @var bool
     */
    private $cacheContent;

    /**
     * @param Client                 $client
     * @param CacheItemPoolInterface $cacheItemPool
     * @param string                 $spaceId
     * @param string                 $environmentId
     * @param bool                   $cacheContent
     */
    public function __construct(
        Client $client,
        CacheItemPoolInterface $cacheItemPool,
        string $spaceId,
        string $environmentId,
        bool $cacheContent = \false
    ) {
        $this->client = $client;
        $this->api = $client->getApi();
        $this->cacheItemPool = $cacheItemPool;
        $this->spaceId = $spaceId;
        $this->environmentId = $environmentId;
        $this->cacheContent = $cacheContent;
    }

    private function mustBeCached(string $type): bool
    {
        if ($this->cacheContent) {
            return \true;
        }

        return !\in_array($type, ['Asset', 'Entry'], \true);
    }

    /**
     * Warm up the locale resource repository with instances fetched from cache.
     *
     * @param string $key
     * @param string $type
     */
    private function warmUp(string $key, string $type)
    {
        if (isset($this->warmupStack[$key]) || isset($this->resources[$key]) || !$this->mustBeCached($type)) {
            return;
        }

        $item = $this->cacheItemPool->getItem($key);
        if ($item->isHit()) {
            $this->warmupStack[$key] = \true;
            /** @var ResourceInterface $resource */
            $resource = $this->client->parseJson($item->get());
            $this->resources[$key] = $resource;
            unset($this->warmupStack[$key]);
        }
    }

    /**
     * @param string      $type
     * @param string      $resourceId
     * @param string|null $locale
     *
     * @return bool
     */
    public function has(string $type, string $resourceId, string $locale = \null): bool
    {
        $key = $this->generateCacheKey($type, $resourceId, $locale);
        $this->warmUp($key, $type);

        return isset($this->resources[$key]);
    }

    /**
     * @param ResourceInterface $resource
     */
    public function set(ResourceInterface $resource)
    {
        /** @var SystemPropertiesInterface $sys */
        $sys = $resource->getSystemProperties();
        $type = $sys->getType();

        $locale = $sys instanceof LocalizedResourceSystemProperties
            ? $sys->getLocale()
            : \null;
        $key = $this->generateCacheKey($type, $sys->getId(), $locale);
        $this->resources[$key] = $resource;

        if (!$this->mustBeCached($type)) {
            return;
        }

        $cacheItem = $this->cacheItemPool->getItem($key);
        if (!$cacheItem->isHit()) {
            $cacheItem->set(guzzle_json_encode($resource));
            $this->cacheItemPool->save($cacheItem);
        }
    }

    /**
     * @param string      $type
     * @param string      $resourceId
     * @param string|null $locale
     *
     * @return ResourceInterface
     */
    public function get(string $type, string $resourceId, string $locale = \null): ResourceInterface
    {
        $key = $this->generateCacheKey($type, $resourceId, $locale);
        $this->warmUp($key, $type);

        return $this->resources[$key];
    }

    /**
     * @param string      $type
     * @param string      $resourceId
     * @param string|null $locale
     *
     * @return string
     */
    public function generateCacheKey(string $type, string $resourceId, string $locale = \null): string
    {
        $locale = \strtr($locale ?: '__ALL__', [
            '-' => '_',
            '*' => '__ALL__',
        ]);

        return \sprintf(
            'contentful.%s.%s.%s.%s.%s.%s',
            $this->api,
            $this->spaceId,
            $this->environmentId,
            $type,
            \str_replace('-', '_', $resourceId),
            $locale
        );
    }
}

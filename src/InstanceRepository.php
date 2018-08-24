<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

use Contentful\Core\Resource\ResourceInterface;
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
     * @var string[]
     */
    private static $warmupTypes = [
        'ContentType',
        'Environment',
        'Space',
    ];

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
     * @var bool
     */
    private $autoWarmup;

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
     * @param Client                 $client
     * @param CacheItemPoolInterface $cacheItemPool
     * @param bool                   $autoWarmup
     * @param string                 $spaceId
     * @param string                 $environmentId
     * @param bool                   $cacheContent
     */
    public function __construct(
        Client $client,
        CacheItemPoolInterface $cacheItemPool,
        $autoWarmup,
        $spaceId,
        $environmentId,
        $cacheContent = false
    ) {
        $this->client = $client;
        $this->api = $client->getApi();
        $this->cacheItemPool = $cacheItemPool;
        $this->autoWarmup = $autoWarmup;
        $this->spaceId = $spaceId;
        $this->environmentId = $environmentId;

        if ($cacheContent) {
            self::$warmupTypes[] = 'Entry';
            self::$warmupTypes[] = 'Asset';
        }
    }

    /**
     * Warm up the locale resource repository with instances fetched from cache.
     *
     * @param string $key
     * @param string $type
     */
    private function warmUp($key, $type)
    {
        if (isset($this->warmupStack[$key]) || isset($this->resources[$key]) || !\in_array($type, self::$warmupTypes, true)) {
            return;
        }

        $item = $this->cacheItemPool->getItem($key);
        if ($item->isHit()) {
            $this->warmupStack[$key] = true;
            $this->resources[$key] = $this->client->parseJson($item->get());
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
    public function has($type, $resourceId, $locale = null)
    {
        $key = $this->generateCacheKey($this->api, $type, $resourceId, $locale);
        $this->warmUp($key, $type);

        return isset($this->resources[$key]);
    }

    /**
     * @param ResourceInterface $resource
     */
    public function set(ResourceInterface $resource)
    {
        /** @var SystemProperties $sys */
        $sys = $resource->getSystemProperties();
        $type = $sys->getType();

        $key = $this->generateCacheKey(
            $this->api,
            $type,
            $sys->getId(),
            $sys->getLocale()
        );

        $this->resources[$key] = $resource;

        if ($this->autoWarmup && \in_array($type, self::$warmupTypes, true)) {
            $cacheItem = $this->cacheItemPool->getItem($key);

            if (!$cacheItem->isHit()) {
                $cacheItem->set(guzzle_json_encode($resource));
                $this->cacheItemPool->save($cacheItem);
            }
        }
    }

    /**
     * @param string      $type
     * @param string      $resourceId
     * @param string|null $locale
     *
     * @return ResourceInterface
     */
    public function get($type, $resourceId, $locale = null)
    {
        $key = $this->generateCacheKey($this->api, $type, $resourceId, $locale);
        $this->warmUp($key, $type);

        return $this->resources[$key];
    }

    /**
     * @param string      $api
     * @param string      $type
     * @param string      $resourceId
     * @param string|null $locale
     *
     * @return string
     */
    public function generateCacheKey($api, $type, $resourceId, $locale = null)
    {
        $locale = \strtr($locale ?: '__ALL__', [
            '-' => '_',
            '*' => '__ALL__',
        ]);

        return \sprintf(
            'contentful.%s.%s.%s.%s.%s.%s',
            $api,
            $this->spaceId,
            $this->environmentId,
            $type,
            \str_replace('-', '_', $resourceId),
            $locale
        );
    }
}

<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

use Contentful\Core\Resource\ResourceInterface;
use Psr\Cache\CacheItemPoolInterface;

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
     * @var array[ResourceInterface[]]
     */
    private $resources = [
        'Asset' => [],
        'ContentType' => [],
        'Entry' => [],
        'Environment' => [],
        'Locale' => [],
        'Space' => [],
    ];

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
     */
    public function __construct(
        Client $client,
        CacheItemPoolInterface $cacheItemPool,
        $autoWarmup,
        $spaceId,
        $environmentId
    ) {
        $this->client = $client;
        $this->api = $client->getApi();
        $this->cacheItemPool = $cacheItemPool;
        $this->autoWarmup = $autoWarmup;
        $this->spaceId = $spaceId;
        $this->environmentId = $environmentId;
    }

    /**
     * Warm up the locale resource repository with instances fetched from cache.
     *
     * @param string $type
     * @param string $resourceId
     */
    private function warmUp($type, $resourceId)
    {
        $key = $this->generateCacheKey($this->api, $type, $resourceId);
        if (isset($this->warmupStack[$key]) || isset($this->resources[$type][$resourceId]) || !\in_array($type, self::$warmupTypes, true)) {
            return;
        }

        $item = $this->cacheItemPool->getItem($key);
        if ($item->isHit()) {
            $this->warmupStack[$key] = true;
            $this->resources[$type][$resourceId] = $this->client->parseJson($item->get());
            unset($this->warmupStack[$key]);
        }
    }

    /**
     * @param string $type
     * @param string $resourceId
     *
     * @return bool
     */
    public function has($type, $resourceId)
    {
        $this->warmUp($type, $resourceId);

        return isset($this->resources[$type][$resourceId]);
    }

    /**
     * @param ResourceInterface $resource
     */
    public function set(ResourceInterface $resource)
    {
        /** @var SystemProperties $sys */
        $sys = $resource->getSystemProperties();
        $type = $sys->getType();
        $resourceId = $sys->getId();

        if ('Entry' === $type || 'Asset' === $type) {
            $locale = $sys->getLocale();

            $resourceId .= '-'.($locale ?: '*');
        }

        $this->resources[$type][$resourceId] = $resource;

        if ($this->autoWarmup && \in_array($type, self::$warmupTypes, true)) {
            $key = $this->generateCacheKey($this->api, $type, $resourceId);
            $cacheItem = $this->cacheItemPool->getItem($key);

            if (!$cacheItem->isHit()) {
                $cacheItem->set(\GuzzleHttp\json_encode($resource));
                $this->cacheItemPool->save($cacheItem);
            }
        }
    }

    /**
     * @param string $type
     * @param string $resourceId
     *
     * @return ResourceInterface
     */
    public function get($type, $resourceId)
    {
        $this->warmUp($type, $resourceId);

        return $this->resources[$type][$resourceId];
    }

    /**
     * @param string $api
     * @param string $type
     * @param string $resourceId
     *
     * @return string
     */
    public function generateCacheKey($api, $type, $resourceId)
    {
        return \sprintf(
            'contentful.%s.%s.%s.%s.%s',
            $api,
            $this->spaceId,
            $this->environmentId,
            $type,
            $resourceId
        );
    }
}

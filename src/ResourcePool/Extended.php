<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\ResourcePool;

use Contentful\Core\Resource\ResourceInterface;
use Contentful\Delivery\Client\JsonDecoderClientInterface;

use function GuzzleHttp\json_encode as guzzle_json_encode;

use Psr\Cache\CacheItemPoolInterface;

/**
 * Extended class.
 *
 * This class acts as a registry for current objects managed by the Client.
 * It also abstracts access to objects stored in cache.
 */
class Extended extends Standard
{
    /**
     * @var JsonDecoderClientInterface
     */
    protected $client;

    /**
     * @var CacheItemPoolInterface
     */
    protected $cacheItemPool;

    /**
     * @var string[]
     */
    protected $warmupTypes = [
        'ContentType',
        'Environment',
        'Space',
    ];

    /**
     * When retrieving from cache, there's the risk of an endless loop,
     * so we keep track of the resources we're currently warming up, and
     * skip the process if we track that we're already in the process of doing so.
     *
     * @var bool[]
     */
    protected $warmupStack = [];

    /**
     * @var bool
     */
    protected $autoWarmup;

    public function __construct(
        JsonDecoderClientInterface $client,
        CacheItemPoolInterface $cacheItemPool,
        bool $autoWarmup = false,
        bool $cacheContent = false
    ) {
        parent::__construct(
            $client->getApi(),
            $client->getSpaceId(),
            $client->getEnvironmentId()
        );
        $this->client = $client;
        $this->cacheItemPool = $cacheItemPool;
        $this->autoWarmup = $autoWarmup;

        if ($cacheContent) {
            $this->warmupTypes[] = 'Entry';
            $this->warmupTypes[] = 'Asset';
        }
    }

    protected function savesResource(string $type): bool
    {
        return true;
    }

    protected function warmUp(string $key, string $type)
    {
        $currentlyWarmingUp = isset($this->warmupStack[$key]);
        $alreadyWarmedUp = isset($this->resources[$key]);
        $shouldWarmUp = \in_array($type, $this->warmupTypes, true);
        if ($currentlyWarmingUp || $alreadyWarmedUp || !$shouldWarmUp) {
            return;
        }

        $item = $this->cacheItemPool->getItem($key);
        if ($item->isHit()) {
            $this->warmupStack[$key] = true;
            /** @var ResourceInterface $resource */
            $resource = $this->client->parseJson($item->get());
            $this->resources[$key] = $resource;
            unset($this->warmupStack[$key]);
        }
    }

    public function save(ResourceInterface $resource): bool
    {
        if (!parent::save($resource)) {
            return false;
        }

        $key = $this->generateKey(
            $resource->getType(),
            $resource->getId(),
            ['locale' => $this->getResourceLocale($resource)]
        );

        if ($this->autoWarmup && \in_array($resource->getType(), $this->warmupTypes, true)) {
            $cacheItem = $this->cacheItemPool->getItem($key);
            if (!$cacheItem->isHit()) {
                $cacheItem->set(guzzle_json_encode($resource));
                $this->cacheItemPool->save($cacheItem);
            }
        }

        return true;
    }
}

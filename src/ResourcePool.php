<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery;

use Contentful\Core\Resource\BaseResourcePool;
use Contentful\Core\Resource\ResourceInterface;
use Contentful\Delivery\SystemProperties\LocalizedResource as LocalizedResourceSystemProperties;
use Psr\Cache\CacheItemPoolInterface;
use function GuzzleHttp\json_encode as guzzle_json_encode;

/**
 * ResourcePool class.
 *
 * This class acts as a registry for current objects managed by the Client.
 * It also abstracts access to objects stored in cache.
 */
class ResourcePool extends BaseResourcePool
{
    /**
     * @var string[]
     */
    private $warmupTypes = [
        'ContentType',
        'Environment',
        'Space',
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
    private $autoWarmup;

    /**
     * @param Client                 $client
     * @param CacheItemPoolInterface $cacheItemPool
     * @param bool                   $autoWarmup
     * @param bool                   $cacheContent
     */
    public function __construct(
        Client $client,
        CacheItemPoolInterface $cacheItemPool,
        bool $autoWarmup = \false,
        bool $cacheContent = \false
    ) {
        $this->client = $client;
        $this->api = $client->getApi();
        $this->cacheItemPool = $cacheItemPool;
        $this->spaceId = $client->getSpaceId();
        $this->environmentId = $client->getEnvironmentId();
        $this->autoWarmup = $autoWarmup;

        if ($cacheContent) {
            $this->warmupTypes[] = 'Entry';
            $this->warmupTypes[] = 'Asset';
        }
    }

    /**
     * Warm up the locale resource repository with instances fetched from cache.
     *
     * @param string $key
     * @param string $type
     */
    private function warmUp(string $key, string $type)
    {
        $currentlyWarmingUp = isset($this->warmupStack[$key]);
        $alreadyWarmedUp = isset($this->resources[$key]);
        $shouldWarmUp = \in_array($type, $this->warmupTypes, \true);
        if ($currentlyWarmingUp || $alreadyWarmedUp || !$shouldWarmUp) {
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
     * {@inheritdoc}
     */
    public function has(string $type, string $id, array $options = []): bool
    {
        $key = $this->generateKey($type, $id, $options);
        $this->warmUp($key, $type);

        if (isset($this->resources[$key])) {
            return true;
        }

        unset($options['locale']);
        $key = $this->generateKey($type, $id, $options);

        return isset($this->resources[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function save(ResourceInterface $resource): bool
    {
        $sys = $resource->getSystemProperties();
        $type = $sys->getType();

        $locale = $sys instanceof LocalizedResourceSystemProperties
            ? $sys->getLocale()
            : \null;
        $key = $this->generateKey($type, $sys->getId(), ['locale' => $locale]);

        if (isset($this->resources[$key])) {
            return \false;
        }

        $this->resources[$key] = $resource;

        if ($this->autoWarmup && \in_array($type, $this->warmupTypes, \true)) {
            $cacheItem = $this->cacheItemPool->getItem($key);
            if (!$cacheItem->isHit()) {
                $cacheItem->set(guzzle_json_encode($resource));
                $this->cacheItemPool->save($cacheItem);
            }
        }

        return \true;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $type, string $id, array $options = []): ResourceInterface
    {
        $locale = $options['locale'] ?? \null;
        $key = $this->generateKey($type, $id, $options);
        $this->warmUp($key, $type);

        if (isset($this->resources[$key])) {
            return $this->resources[$key];
        }

        unset($options['locale']);
        $key = $this->generateKey($type, $id, $options);

        if (isset($this->resources[$key])) {
            return $this->resources[$key];
        }

        throw new \OutOfBoundsException(\sprintf(
            'Resource pool could not find a resource with type "%s", ID "%s"%s.',
            $type,
            $id,
            $locale ? ', and locale "'.$locale.'"' : ''
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function generateKey(string $type, string $id, array $options = []): string
    {
        $locale = \strtr($options['locale'] ?? '__ALL__', [
            '-' => '_',
            '*' => '__ALL__',
        ]);

        return \sprintf(
            'contentful.%s.%s.%s.%s.%s.%s',
            $this->api,
            $this->spaceId,
            $this->environmentId,
            $type,
            $this->sanitize($id),
            $locale
        );
    }
}

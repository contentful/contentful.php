<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Cache;

use Contentful\Delivery\Client;
use Contentful\Delivery\InstanceRepository;
use Contentful\Delivery\Query;
use Psr\Cache\CacheItemPoolInterface;

/**
 * CacheClearer class.
 *
 * Use this class to clear the needed cache information from a
 * PSR-6 compatible pool.
 */
class CacheClearer
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var CacheItemPoolInterface
     */
    private $cacheItemPool;

    /**
     * CacheClearer constructor.
     *
     * @param Client                 $client
     * @param CacheItemPoolInterface $cacheItemPool
     */
    public function __construct(Client $client, CacheItemPoolInterface $cacheItemPool)
    {
        $this->client = $client;
        $this->cacheItemPool = $cacheItemPool;
    }

    /**
     * @return bool
     */
    public function clear()
    {
        $api = $this->client->getApi();
        $space = $this->client->getSpace();
        $environment = $this->client->getEnvironment();

        $keys = [
            InstanceRepository::generateCacheKey($api, 'Space', $space->getId()),
            InstanceRepository::generateCacheKey($api, 'Environment', $environment->getId()),
        ];

        $query = (new Query())
            ->setLimit(100);
        $contentTypes = $this->client->getContentTypes($query);

        foreach ($contentTypes as $contentType) {
            $keys[] = InstanceRepository::generateCacheKey($api, 'ContentType', $contentType->getId());
        }

        return $this->cacheItemPool->deleteItems($keys);
    }
}

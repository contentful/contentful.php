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
 * CacheWarmer class.
 *
 * Use this class to save the needed cache information in a
 * PSR-6 compatible pool.
 */
class CacheWarmer
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
     * CacheWarmer constructor.
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
    public function warmUp()
    {
        $api = $this->client->getApi();

        $space = $this->client->getSpace();
        $item = $this->cacheItemPool->getItem(InstanceRepository::generateCacheKey($api, 'Space', $space->getId()));
        $item->set(\json_encode($space));
        $this->cacheItemPool->saveDeferred($item);

        $query = (new Query())
            ->setLimit(100);
        $contentTypes = $this->client->getContentTypes($query);

        foreach ($contentTypes as $contentType) {
            $item = $this->cacheItemPool->getItem(InstanceRepository::generateCacheKey($api, 'ContentType', $contentType->getId()));
            $item->set(\json_encode($contentType));
            $this->cacheItemPool->saveDeferred($item);
        }

        return $this->cacheItemPool->commit();
    }
}

<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Cache;

use Contentful\Delivery\Client;
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

        $query = (new Query())
            ->setLimit(100);
        $contentTypes = $this->client->getContentTypes($query);

        $spaceItem = $this->cacheItemPool->getItem(\Contentful\cache_key_space($api, $space->getId()));
        $spaceItem->set(\json_encode($space));
        $this->cacheItemPool->saveDeferred($spaceItem);

        foreach ($contentTypes as $contentType) {
            $spaceItem = $this->cacheItemPool->getItem(\Contentful\cache_key_content_type($api, $contentType->getId()));
            $spaceItem->set(\json_encode($contentType));
            $this->cacheItemPool->saveDeferred($spaceItem);
        }

        return $this->cacheItemPool->commit();
    }
}

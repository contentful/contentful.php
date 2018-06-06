<?php

/**
 * This file is part of the contentful/contentful package.
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
        $instanceRepository = $this->client->getInstanceRepository();

        $space = $this->client->getSpace();
        $item = $this->cacheItemPool->getItem(
            $instanceRepository->generateCacheKey($api, 'Space', $space->getId())
        );
        $item->set(\json_encode($space));
        $this->cacheItemPool->saveDeferred($item);

        $environment = $this->client->getEnvironment();
        $item = $this->cacheItemPool->getItem(
            $instanceRepository->generateCacheKey($api, 'Environment', $environment->getId())
        );
        $item->set(\json_encode($environment));
        $this->cacheItemPool->saveDeferred($item);

        $query = (new Query())
            ->setLimit(100);
        $contentTypes = $this->client->getContentTypes($query);

        foreach ($contentTypes as $contentType) {
            $item = $this->cacheItemPool->getItem(
                $instanceRepository->generateCacheKey($api, 'ContentType', $contentType->getId())
            );
            $item->set(\json_encode($contentType));
            $this->cacheItemPool->saveDeferred($item);
        }

        return $this->cacheItemPool->commit();
    }
}

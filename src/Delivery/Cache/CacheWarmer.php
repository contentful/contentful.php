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
use Symfony\Component\Filesystem\Filesystem;

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
     * @param Client $client
     * @param CacheItemPoolInterface $cacheItemPool
     */
    public function __construct(Client $client, CacheItemPoolInterface $cacheItemPool)
    {
        $this->client = $client;
        $this->cacheItemPool = $cacheItemPool;
    }

    /**
     * @param string $cacheDir
     */
    public function warmUp()
    {
        $space = $this->client->getSpace();

        $query = (new Query())
            ->setLimit(100);

        $contentTypes = $this->client->getContentTypes($query);

        $spaceItem = $this->cacheItemPool->getItem(CacheKeyGenerator::getSpaceKey());
        $spaceItem->set(\json_encode($space));
        $this->cacheItemPool->saveDeferred($spaceItem);

        foreach ($contentTypes as $contentType) {
            $spaceItem = $this->cacheItemPool->getItem(CacheKeyGenerator::getContentTypeKey($contentType->getId()));
            $spaceItem->set(\json_encode($contentType));
            $this->cacheItemPool->saveDeferred($spaceItem);
        }

        $this->cacheItemPool->commit();
    }
}

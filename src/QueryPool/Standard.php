<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\QueryPool;

use Contentful\Core\Api\BaseQuery;
use Contentful\Core\Resource\ResourceArray;
use Contentful\Delivery\Client\JsonDecoderClientInterface;
use Contentful\Delivery\QueryPoolInterface;

use function GuzzleHttp\json_encode as guzzle_json_encode;

use Psr\Cache\CacheItemPoolInterface;

class Standard implements QueryPoolInterface
{
    /**
     * @var JsonDecoderClientInterface
     */
    private $client;

    /**
     * @var CacheItemPoolInterface
     */
    private $cacheItemPool;

    /**
     * @var ResourceArray[]
     */
    private $queries = [];

    /**
     * @var int
     */
    private $lifetime;

    public function __construct(
        JsonDecoderClientInterface $client,
        CacheItemPoolInterface $cacheItemPool,
        int $lifetime = 0
    ) {
        $this->client = $client;
        $this->cacheItemPool = $cacheItemPool;
        $this->lifetime = $lifetime;
    }

    public function save(?BaseQuery $query, ResourceArray $entries): bool
    {
        $key = $this->generateKey($query);
        $alreadyExists = isset($this->queries[$key]);
        $this->queries[$key] = $entries;

        if ($alreadyExists) {
            return false;
        }

        if (0 === $this->lifetime) {
            return true;
        }

        $cacheItem = $this->cacheItemPool->getItem($key);
        if (!$cacheItem->isHit()) {
            $cacheItem->set(guzzle_json_encode($entries));
            $cacheItem->expiresAfter($this->lifetime);
            $this->cacheItemPool->save($cacheItem);
        }

        return true;
    }

    public function get(?BaseQuery $query): ResourceArray
    {
        $key = $this->generateKey($query);
        $this->warmUp($key);

        if (!isset($this->queries[$key])) {
            throw new \OutOfBoundsException(sprintf('Query pool could not find a query with key "%s".', $key));
        }

        return $this->queries[$key];
    }

    public function has(?BaseQuery $query): bool
    {
        $key = $this->generateKey($query);
        $this->warmUp($key);

        return isset($this->queries[$key]);
    }

    public function generateKey(?BaseQuery $query): string
    {
        return 'QUERY__'.sha1(serialize($query));
    }

    protected function warmUp(string $key): void
    {
        if (0 === $this->lifetime) {
            return;
        }

        $item = $this->cacheItemPool->getItem($key);

        if ($item->isHit()) {
            $resourceArray = $this->client->parseJson($item->get());
            if (!$resourceArray instanceof ResourceArray) {
                throw new \RuntimeException(sprintf('Invalid query cache hit. Expected to be "%s", "%s" given.', ResourceArray::class, $resourceArray::class));
            }
            $this->queries[$key] = $resourceArray;
        }
    }
}

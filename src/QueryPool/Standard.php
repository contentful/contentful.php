<?php

namespace Contentful\Delivery\QueryPool;

use Contentful\Core\Api\BaseQuery;
use Contentful\Core\Resource\ResourceArray;
use Contentful\Delivery\Client\JsonDecoderClientInterface;
use Contentful\Delivery\QueryPoolInterface;
use Psr\Cache\CacheItemPoolInterface;
use function GuzzleHttp\json_encode as guzzle_json_encode;

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

    /**
     * @inheritDoc
     */
    public function save(?BaseQuery $query, ResourceArray $entries): bool
    {
        $key = $this->generateKey($query);
        $alreadyExists = isset($this->queries[$key]);
        $this->queries[$key] = $entries;

        if ($alreadyExists) {
            return false;
        }

        if ($this->lifetime === 0) {
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
            throw new \OutOfBoundsException(\sprintf('Query pool could not find a query with key "%s".', $key));
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
        return 'QUERY__' . sha1(serialize($query));
    }

    protected function warmUp(string $key): void
    {
        if ($this->lifetime === 0) {
            return;
        }

        $item = $this->cacheItemPool->getItem($key);

        if ($item->isHit()) {
            $resourceArray = $this->client->parseJson($item->get());
            if (!$resourceArray instanceof  ResourceArray) {
                throw new \RuntimeException(
                    sprintf(
                        'Invalid query cache hit. Expected to be "%s", "%s" given.',
                        ResourceArray::class,
                        is_object($resourceArray) ? get_class($resourceArray) : gettype($resourceArray)
                    )
                );
            }
            $this->queries[$key] = $resourceArray;
        }
    }
}

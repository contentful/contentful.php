<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Cache;

use Contentful\Core\Resource\ResourceInterface;
use Contentful\Core\Resource\ResourcePoolInterface;
use Contentful\Delivery\Client\ClientInterface;
use Contentful\Delivery\Query;
use Contentful\Delivery\Resource\Locale;
use Psr\Cache\CacheItemPoolInterface;

abstract class BaseCacheHandler
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var ResourcePoolInterface
     */
    protected $resourcePool;

    /**
     * @var CacheItemPoolInterface
     */
    protected $cacheItemPool;

    /**
     * CacheWarmer constructor.
     */
    public function __construct(
        ClientInterface $client,
        ResourcePoolInterface $resourcePool,
        CacheItemPoolInterface $cacheItemPool
    ) {
        $this->client = $client;
        $this->resourcePool = $resourcePool;
        $this->cacheItemPool = $cacheItemPool;
    }

    /**
     * @return \Generator|ResourceInterface[]
     */
    protected function fetchResources(bool $cacheContent = false)
    {
        $resources = [
            $this->client->getSpace(),
            $this->client->getEnvironment(),
        ];

        $query = (new Query())
            ->setLimit(100)
        ;
        foreach ($this->client->getContentTypes($query) as $contentType) {
            $resources[] = $contentType;
        }

        foreach ($resources as $resource) {
            yield $resource;
        }

        if ($cacheContent) {
            $locales = array_map(function (Locale $locale) {
                return $locale->getCode();
            }, $this->client->getEnvironment()->getLocales());
            $locales[] = '*';

            foreach ($this->fetchCollection('Entry', $locales) as $entry) {
                yield $entry;
            }

            foreach ($this->fetchCollection('Asset', $locales) as $asset) {
                yield $asset;
            }
        }
    }

    /**
     * @param string   $type    Either 'Entry' or 'Asset'
     * @param string[] $locales
     */
    private function fetchCollection(string $type, array $locales): \Generator
    {
        foreach ($locales as $locale) {
            $skip = 0;
            do {
                $query = (new Query())
                    ->setLocale($locale)
                    ->setLimit(1000)
                    ->setSkip($skip)
                ;
                $resources = 'Entry' === $type
                    ? $this->client->getEntries($query)
                    : $this->client->getAssets($query);

                foreach ($resources as $resource) {
                    yield $resource;
                }

                $skip += 1000;
            } while ($resources->getTotal() > $resources->getSkip() + 1000);
        }
    }
}

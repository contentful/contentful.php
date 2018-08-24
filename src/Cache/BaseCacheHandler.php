<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Cache;

use Contentful\Core\Resource\ResourceInterface;
use Contentful\Delivery\Client;
use Contentful\Delivery\InstanceRepository;
use Contentful\Delivery\Query;
use Contentful\Delivery\Resource\Locale;
use Psr\Cache\CacheItemPoolInterface;

abstract class BaseCacheHandler
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var CacheItemPoolInterface
     */
    protected $cacheItemPool;

    /**
     * @var \Closure
     */
    protected $toggler;

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

        $this->toggler = \Closure::bind(function (InstanceRepository $instanceRepository, $value) {
            $previous = $instanceRepository->autoWarmup;
            $instanceRepository->autoWarmup = (bool) $value;

            return $previous;
        }, null, InstanceRepository::class);
    }

    /**
     * @param InstanceRepository $instanceRepository
     * @param bool               $value
     *
     * @return bool
     */
    protected function toggleAutoWarmup(InstanceRepository $instanceRepository, $value)
    {
        $toggler = $this->toggler;

        return $toggler($instanceRepository, $value);
    }

    /**
     * @param bool $cacheContent
     *
     * @return ResourceInterface[]
     */
    protected function fetchResources($cacheContent = false)
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
            $locales = \array_map(function (Locale $locale) {
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
     *
     * @return \Generator
     */
    private function fetchCollection($type, $locales)
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

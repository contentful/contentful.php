<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery;

use Contentful\Core\Api\Link;
use Contentful\Core\Api\LinkResolverInterface;
use Contentful\Core\Resource\ResourceInterface;
use Contentful\Core\Resource\ResourcePoolInterface;
use Contentful\Delivery\Client\ClientInterface;

class LinkResolver implements LinkResolverInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var ResourcePoolInterface
     */
    private $resourcePool;

    /**
     * LinkResolver constructor.
     */
    public function __construct(ClientInterface $client, ResourcePoolInterface $resourcePool)
    {
        $this->client = $client;
        $this->resourcePool = $resourcePool;
    }

    public function resolveLink(Link $link, array $parameters = []): ResourceInterface
    {
        $locale = $parameters['locale'] ?? null;

        switch ($link->getLinkType()) {
            case 'Asset':
                return $this->client->getAsset($link->getId(), $locale);
            case 'ContentType':
                return $this->client->getContentType($link->getId());
            case 'Entry':
                return $this->client->getEntry($link->getId(), $locale);
            case 'Environment':
                return $this->client->getEnvironment();
            case 'Space':
                return $this->client->getSpace();
            case 'Tag':
                return $this->client->getTag($link->getId());
            default:
                throw new \InvalidArgumentException(sprintf('Trying to resolve link for unknown type "%s".', $link->getLinkType()));
        }
    }

    public function resolveLinkCollection(array $links, array $parameters = []): array
    {
        $locale = $parameters['locale'] ?? null;

        // We load all resources for the given resource types
        $types = array_unique(array_map(function (Link $link) {
            return $link->getLinkType();
        }, $links));

        $resources = [];
        foreach ($types as $type) {
            $resources = array_merge($resources, $this->resolveLinksForResourceType($type, $links, $locale));
        }

        $collection = [];
        foreach ($links as $link) {
            $key = $link->getLinkType().'.'.$link->getId();

            if (isset($resources[$key])) {
                $collection[] = $resources[$key];
            }
        }

        return $collection;
    }

    /**
     * Loads resources for a certain type only.
     *
     * @param Link[] $links
     *
     * @return ResourceInterface[]
     */
    private function resolveLinksForResourceType(string $type, array $links, ?string $locale = null): array
    {
        $resourceIds = array_map(function (Link $link): string {
            return $link->getId();
        }, array_filter($links, function (Link $link) use ($type): bool {
            return $link->getLinkType() === $type;
        }));

        $resources = [];
        $collection = $this->fetchResourcesForGivenIds($resourceIds, $type, $locale);
        foreach ($collection as $resource) {
            $resources[$type.'.'.$resource->getId()] = $resource;
        }

        return $resources;
    }

    /**
     * Loads resources in the current cache pool and fetches the missing ones from the API.
     *
     * @return ResourceInterface[]
     */
    private function fetchResourcesForGivenIds(array $resourceIds, string $type, ?string $locale = null): array
    {
        $resources = [];
        $resourcePoolOptions = ['locale' => $locale];
        foreach ($resourceIds as $index => $resourceId) {
            if ($this->resourcePool->has($type, $resourceId, $resourcePoolOptions)) {
                $resources[] = $this->resourcePool->get($type, $resourceId, $resourcePoolOptions);

                unset($resourceIds[$index]);
            }
        }

        foreach ($this->createIdChunks($resourceIds) as $chunk) {
            $resources = array_merge($resources, $this->fetchCollectionFromApi($chunk, $type, $locale));
        }

        return $resources;
    }

    /**
     * Each Contentful ID can be up to 64 characters long,
     * so if we have too many links in a single query it might create some queries
     * that are too long.
     *
     * For this reason, we split the IDs into smaller chunks with at most 30 elements each,
     * so we're sure we stay under the 2000 characters per URL.
     *
     * @param string[] $resourceIds
     *
     * @return string[][]
     */
    private function createIdChunks(array $resourceIds): array
    {
        $chunks = [];
        $chunkId = -1;
        $resourceIds = array_values($resourceIds);
        foreach ($resourceIds as $index => $resourceId) {
            if (0 === $index % 30) {
                ++$chunkId;
                $chunks[$chunkId] = [];
            }

            $chunks[$chunkId][] = $resourceId;
        }

        return $chunks;
    }

    /**
     * Actually make the relevant API calls.
     *
     * @param string[] $resourceIds
     *
     * @return ResourceInterface[]
     */
    private function fetchCollectionFromApi(array $resourceIds, string $type, ?string $locale = null): array
    {
        $query = (new Query())
            ->where('sys.id[in]', $resourceIds)
        ;

        if ('Asset' === $type || 'Entry' === $type) {
            $query->setLocale($locale);
        }

        switch ($type) {
            case 'Asset':
                return $this->client->getAssets($query)->getItems();
            case 'ContentType':
                return $this->client->getContentTypes($query)->getItems();
            case 'Entry':
                return $this->client->getEntries($query)->getItems();
            case 'Environment':
                return [$this->client->getEnvironment()];
            case 'Space':
                return [$this->client->getSpace()];
            default:
                throw new \InvalidArgumentException(sprintf('Trying to resolve link for unknown type "%s".', $type));
        }
    }
}

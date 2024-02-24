<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Client;

use Contentful\Core\Api\Link;
use Contentful\Core\Exception\NotFoundException;
use Contentful\Core\Resource\ResourceArray;
use Contentful\Core\Resource\ResourceInterface;
use Contentful\Delivery\Query;
use Contentful\Delivery\Resource\Asset;
use Contentful\Delivery\Resource\ContentType;
use Contentful\Delivery\Resource\Entry;
use Contentful\Delivery\Resource\Environment;
use Contentful\Delivery\Resource\Space;
use Contentful\Delivery\Resource\Tag;

/**
 * ClientInterface.
 *
 * This interface should be used whenever referring to a client object instance,
 * as it decouples the method signatures from the actual implementation.
 *
 * It provides definitions for all methods which return API resources.
 */
interface ClientInterface extends ScopedClientInterface
{
    /**
     * Returns a single Asset object corresponding to the given ID.
     *
     * @throws NotFoundException If no asset is found with the given ID
     */
    public function getAsset(string $assetId, ?string $locale = null): Asset;

    /**
     * Returns a collection of Asset objects wrapped in a ResourceArray instance.
     *
     * @return ResourceArray|Asset[]
     */
    public function getAssets(?Query $query = null): ResourceArray;

    /**
     * Returns a single ContentType object corresponding to the given ID.
     *
     * @throws NotFoundException If no content type is found with the given ID
     */
    public function getContentType(string $contentTypeId): ContentType;

    /**
     * Returns a collection of ContentType objects wrapped in a ResourceArray instance.
     *
     * @return ResourceArray|ContentType[]
     */
    public function getContentTypes(?Query $query = null): ResourceArray;

    /**
     * Returns the Environment object corresponding to the one in use.
     */
    public function getEnvironment(): Environment;

    /**
     * Returns a single Entry object corresponding to the given ID.
     *
     * @throws NotFoundException If no entry is found with the given ID
     */
    public function getEntry(string $entryId, ?string $locale = null): Entry;

    /**
     * Returns a collection of Entry objects wrapped in a ResourceArray instance.
     *
     * @return ResourceArray|Entry[]
     */
    public function getEntries(?Query $query = null): ResourceArray;

    /**
     * Returns the Space object corresponding to the one in use.
     */
    public function getSpace(): Space;

    /**
     * Find a specific tag by its id.
     *
     * @param string $tagId the id of the tag
     */
    public function getTag(string $tagId): Tag;

    /**
     * Returns all tags in the current space and environment.
     *
     * @return Tag[]
     */
    public function getAllTags(): array;

    /**
     * Resolve a link to its actual resource.
     *
     * @throws \InvalidArgumentException when encountering an unexpected link type
     */
    public function resolveLink(Link $link, ?string $locale = null): ResourceInterface;

    /**
     * Resolves an array of links.
     * A method implementing this may apply some optimizations
     * to reduce the amount of necessary API calls.
     *
     * @param Link[] $links
     *
     * @return ResourceInterface[]
     */
    public function resolveLinkCollection(array $links, ?string $locale = null): array;
}

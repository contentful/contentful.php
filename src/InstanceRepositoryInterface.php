<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery;

use Contentful\Core\Resource\ResourceInterface;

interface InstanceRepositoryInterface
{
    /**
     * Returns whether the repository contains the given resource.
     *
     * @param string      $type
     * @param string      $resourceId
     * @param string|null $locale
     *
     * @return bool
     */
    public function has(string $type, string $resourceId, string $locale = \null): bool;

    /**
     * Adds the given resource to the repository.
     *
     * @param ResourceInterface $resource
     *
     * @return bool True is the resource was successfully added, false if it was already present
     */
    public function set(ResourceInterface $resource): bool;

    /**
     * Returns the resource for the given key.
     *
     * @param string      $type
     * @param string      $resourceId
     * @param string|null $locale
     *
     * @throws \OutOfBoundsException If the given key does not represent any stored resource
     *
     * @return ResourceInterface
     */
    public function get(string $type, string $resourceId, string $locale = \null): ResourceInterface;

    /**
     * Generates a unique key for the given data.
     *
     * @param string      $type
     * @param string      $resourceId
     * @param string|null $locale
     *
     * @return string
     */
    public function generateKey(string $type, string $resourceId, string $locale = \null): string;
}

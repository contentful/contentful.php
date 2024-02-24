<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\ResourcePool;

use Contentful\Core\Resource\BaseResourcePool;
use Contentful\Core\Resource\ResourceInterface;
use Contentful\Delivery\SystemProperties\LocalizedResource as LocalizedResourceSystemProperties;

/**
 * Standard class.
 *
 * This class acts as a registry for current objects managed by the Client,
 * but only stores space, environment, and content types.
 * Everything else is skipped.
 */
class Standard extends BaseResourcePool
{
    /**
     * @var string
     */
    protected $api;

    /**
     * @var string
     */
    protected $spaceId;

    /**
     * @var string
     */
    protected $environmentId;

    /**
     * Simple constructor.
     */
    public function __construct(string $api, string $spaceId, string $environmentId)
    {
        $this->api = $api;
        $this->spaceId = $spaceId;
        $this->environmentId = $environmentId;
    }

    /**
     * Determines whether the given resource type must be actually stored.
     */
    protected function savesResource(string $type): bool
    {
        return \in_array($type, ['ContentType', 'Environment', 'Space'], true);
    }

    /**
     * @return string|null
     */
    protected function getResourceLocale(ResourceInterface $resource)
    {
        $sys = $resource->getSystemProperties();

        return $sys instanceof LocalizedResourceSystemProperties
            ? $sys->getLocale()
            : null;
    }

    /**
     * Skeleton method which a can be overridden.
     */
    protected function warmUp(string $key, string $type)
    {
    }

    public function has(string $type, string $id, array $options = []): bool
    {
        if (!$this->savesResource($type)) {
            return false;
        }

        $key = $this->generateKey($type, $id, $options);
        $this->warmUp($key, $type);

        return isset($this->resources[$key]);
    }

    public function save(ResourceInterface $resource): bool
    {
        if (!$this->savesResource($resource->getType())) {
            return false;
        }

        $key = $this->generateKey(
            $resource->getType(),
            $resource->getId(),
            ['locale' => $this->getResourceLocale($resource)]
        );

        $exists = isset($this->resources[$key]);
        $this->resources[$key] = $resource;

        return !$exists;
    }

    public function get(string $type, string $id, array $options = []): ResourceInterface
    {
        $locale = $options['locale'] ?? null;
        $key = $this->generateKey($type, $id, $options);
        $this->warmUp($key, $type);

        if (!$this->savesResource($type) || !isset($this->resources[$key])) {
            throw new \OutOfBoundsException(sprintf('Resource pool could not find a resource with type "%s", ID "%s"%s.', $type, $id, $locale ? ', and locale "'.$locale.'"' : ''));
        }

        return $this->resources[$key];
    }

    public function generateKey(string $type, string $id, array $options = []): string
    {
        $locale = strtr($options['locale'] ?? '__ALL__', [
            '-' => '_',
            '*' => '__ALL__',
        ]);

        return 'contentful.'
            .$this->api
            .'.'
            .$this->spaceId
            .'.'
            .$this->environmentId
            .'.'
            .$type
            .'.'
            .$this->sanitize($id)
            .'.'
            .$locale;
    }
}

<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

class InstanceCache
{
    /**
     * @var Space|null
     */
    private $space;

    /**
     * @var Asset[]
     */
    private $assets = [];

    /**
     * @var ContentType[]
     */
    private $contentTypes = [];

    /**
     * @var DynamicEntry[]
     */
    private $entries = [];

    /**
     * InstanceCache constructor.
     *
     * Currently empty but exists for forward compatability.
     */
    public function __construct()
    {
    }

    /**
     * Get the space to which this instance Cache belongs.
     *
     * @return Space|null
     */
    public function getSpace()
    {
        return $this->space;
    }

    /**
     * Set the space to which this instance Cache belongs.
     *
     * @param Space $space
     */
    public function setSpace(Space $space)
    {
        $this->space = $space;
    }

    /**
     * Whether the space has been cached or not
     *
     * @return bool
     */
    public function hasSpace()
    {
        return $this->space !== null;
    }

    /**
     * Get the asset with the specified ID out of the cache
     *
     * @param  string $id
     *
     * @return Asset|null
     */
    public function getAsset($id)
    {
        if (!isset($this->assets[$id])) {
            return null;
        }

        return $this->assets[$id];
    }

    /**
     * @param  string $id
     *
     * @return bool
     */
    public function hasAsset($id)
    {
        return isset($this->assets[$id]);
    }

    /**
     * @param Asset $asset
     */
    public function addAsset(Asset $asset)
    {
        $this->assets[$asset->getId()] = $asset;
    }

    /**
     * @param  string $id
     *
     * @return ContentType|null
     */
    public function getContentType($id)
    {
        if (!isset($this->contentTypes[$id])) {
            return null;
        }

        return $this->contentTypes[$id];
    }

    /**
     * @param  string $id
     *
     * @return bool
     */
    public function hasContentType($id)
    {
        return isset($this->contentTypes[$id]);
    }

    /**
     * @param ContentType $contentType
     */
    public function addContentType(ContentType $contentType)
    {
        $this->contentTypes[$contentType->getId()] = $contentType;
    }

    /**
     * @param  string $id
     *
     * @return DynamicEntry|null
     */
    public function getEntry($id)
    {
        if (!isset($this->entries[$id])) {
            return null;
        }

        return $this->entries[$id];
    }

    /**
     * @param  string $id
     *
     * @return bool
     */
    public function hasEntry($id)
    {
        return isset($this->entries[$id]);
    }

    /**
     * @param DynamicEntry $entry
     */
    public function addEntry(DynamicEntry $entry)
    {
        $this->entries[$entry->getId()] = $entry;
    }
}

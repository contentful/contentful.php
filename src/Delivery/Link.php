<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

/**
 * A link encodes a reference to a resource.
 *
 * @package Contentful\Delivery
 */
class Link
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $linkType;

    /**
     * Link constructor.
     *
     * @param string $id
     * @param string $linkType
     */
    public function __construct($id, $linkType)
    {
        $this->id = $id;
        $this->linkType = $linkType;
    }

    /**
     * Get the ID of the referenced resource
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the type of the Link.
     *
     * Possible values are:
     *  - Asset
     *  - Entry
     *
     * @return string
     */
    public function getLinkType()
    {
        return $this->linkType;
    }
}

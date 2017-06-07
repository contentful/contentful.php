<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful;

/**
 * A link encodes a reference to a resource.
 */
class Link implements \JsonSerializable
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

    /**
     * Returns an object to be used by `json_encode` to serialize objects of this class.
     *
     * @return object
     *
     * @see http://php.net/manual/en/jsonserializable.jsonserialize.php JsonSerializable::jsonSerialize
     */
    public function jsonSerialize()
    {
        return (object) [
            'sys' => (object) [
                'type' => 'Link',
                'id' => $this->id,
                'linkType' => $this->linkType
            ]
        ];
    }
}

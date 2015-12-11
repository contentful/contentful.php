<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

/**
 * A SystemProperties instance contains the metadata of a resource.
 *
 * @package Contentful\Delivery
 */
class SystemProperties implements \JsonSerializable
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var Space|null
     */
    private $space;

    /**
     * @var ContentType|null
     */
    private $contentType;

    /**
     * @var int|null
     */
    private $revision;

    /**
     * @var \DateTimeImmutable|null
     */
    private $createdAt;

    /**
     * @var \DateTimeImmutable|null
     */
    private $updatedAt;

    /**
     * @var \DateTimeImmutable|null
     */
    private $deletedAt;

    /**
     * SystemProperties constructor.
     *
     * @param string                  $id
     * @param string                  $type
     * @param Space|null              $space
     * @param ContentType|null        $contentType
     * @param int|null                $revision
     * @param \DateTimeImmutable|null $createdAt
     * @param \DateTimeImmutable|null $updatedAt
     * @param \DateTimeImmutable|null $deletedAt
     */
    public function __construct($id, $type, Space $space = null, ContentType $contentType = null, $revision = null,
                                \DateTimeImmutable $createdAt = null, \DateTimeImmutable $updatedAt = null,
                                \DateTimeImmutable $deletedAt = null)
    {
        $this->id = $id;
        $this->type = $type;
        $this->space = $space;
        $this->contentType = $contentType;
        $this->revision = $revision;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return Space|null
     */
    public function getSpace()
    {
        return $this->space;
    }

    /**
     * @return ContentType|null
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @return int|null
     */
    public function getRevision()
    {
        return $this->revision;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
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
        $obj = new \stdClass;

        if ($this->id !== null) {
            $obj->id = $this->id;
        }
        if ($this->type !== null) {
            $obj->type = $this->type;
        }
        if ($this->space !== null) {
            $obj->space = (object) [
                'sys' => (object) [
                    'type' => 'Link',
                    'linkType' => 'Space',
                    'id' => $this->space->getId()
                ]
            ];
        }
        if ($this->contentType !== null) {
            $obj->contentType = (object) [
                'sys' => (object) [
                    'type' => 'Link',
                    'linkType' => 'ContentType',
                    'id' => $this->contentType->getId()
                ]
            ];
        }
        if ($this->revision !== null) {
            $obj->revision = $this->revision;
        }
        if ($this->createdAt !== null) {
            $obj->createdAt = $this->formatDateForJson($this->createdAt);
        }
        if ($this->updatedAt !== null) {
            $obj->updatedAt = $this->formatDateForJson($this->updatedAt);
        }
        if ($this->deletedAt !== null) {
            $obj->deletedAt = $this->formatDateForJson($this->deletedAt);
        }

        return $obj;
    }

    /**
     * Unfortunately PHP has no eeasy way to create a nice, ISO 8601 formatted date string with miliseconds and Z
     * as the time zone specifier. Thus this hack.
     *
     * @param  \DateTimeImmutable $dt
     *
     * @return string ISO 8601 formatted date
     */
    private function formatDateForJson(\DateTimeImmutable $dt)
    {
        $dt = $dt->setTimezone(new \DateTimeZone('Etc/UTC'));
        return $dt->format('Y-m-d\TH:i:s.') . str_pad(floor($dt->format('u')/1000), 3, '0', STR_PAD_LEFT) . 'Z';
    }
}

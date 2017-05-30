<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

/**
 * Content Types are schemas that define the fields of Entries. Every Entry can only contain values in the fields
 * defined by it's Content Type, and the values of those fields must match the data type defined in the Content Type.
 */
class ContentType implements \JsonSerializable
{
    /**
     * Name of the Content Type.
     *
     * @var string
     */
    private $name;

    /**
     * Description of the Content Type.
     *
     * @var string|null
     */
    private $description;

    /**
     * The fields, keyed by ID.
     *
     * @var \Contentful\Delivery\ContentTypeField[]
     */
    private $fields = [];

    /**
     * ID of main Field used for display.
     *
     * @var string|null
     */
    private $displayField;

    /**
     * @var SystemProperties
     */
    private $sys;

    /**
     * ContentType constructor.
     *
     * @param  string                                  $name
     * @param  string|null                             $description
     * @param  \Contentful\Delivery\ContentTypeField[] $fields
     * @param  string|null                             $displayField
     * @param  SystemProperties                        $sys
     */
    public function __construct($name, $description, array $fields, $displayField = null, SystemProperties $sys)
    {
        $this->name = $name;
        $this->description = $description;
        foreach ($fields as $field) {
            $this->fields[$field->getId()] = $field;
        }
        $this->displayField = $displayField;
        $this->sys = $sys;
    }

    /**
     * Returns the name of this content type.
     *
     * @return string
     *
     * @api
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns all the fields of this content type as an associative array. The key is the ID of the field.
     *
     * @return \Contentful\Delivery\ContentTypeField[]
     *
     * @api
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Returns the content type's description.
     *
     * @return string|null
     *
     * @api
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the field for the passed id.
     *
     * If the field does not exist, null is returned.
     *
     * @param  string  $fieldId
     *
     * @return \Contentful\Delivery\ContentTypeField|null
     *
     * @api
     */
    public function getField($fieldId)
    {
        if (!isset($this->fields[$fieldId])) {
            return null;
        }

        return $this->fields[$fieldId];
    }

    /**
     * Returns the the display field of a content type. Commonly this is the title.
     *
     * Returns null if not display field is set.
     *
     * @return \Contentful\Delivery\ContentTypeField|null
     *
     * @api
     */
    public function getDisplayField()
    {
        if (null === $this->displayField) {
            return null;
        }

        return $this->getField($this->displayField);
    }

    /**
     * Returns the ID of this content type.
     *
     * @return string
     *
     * @api
     */
    public function getId()
    {
        return $this->sys->getId();
    }

    /**
     * Returns the revision of this content type.
     *
     * @return int
     *
     * @api
     */
    public function getRevision()
    {
        return $this->sys->getRevision();
    }

    /**
     * Returns the time when this content type was last updated.
     *
     * @return \DateTimeImmutable
     *
     * @api
     */
    public function getUpdatedAt()
    {
        return $this->sys->getUpdatedAt();
    }

    /**
     * Returns the time when this content type was created.
     *
     * @return \DateTimeImmutable
     *
     * @api
     */
    public function getCreatedAt()
    {
        return $this->sys->getCreatedAt();
    }

    /**
     * Returns the space this content type belongs to.
     *
     * @return Space
     *
     * @api
     */
    public function getSpace()
    {
        return $this->sys->getSpace();
    }

    /**
     * Returns an object to be used by `json_encode` to serialize objects of this class.
     *
     * @return object
     *
     * @see http://php.net/manual/en/jsonserializable.jsonserialize.php JsonSerializable::jsonSerialize
     *
     * @api
     */
    public function jsonSerialize()
    {
        return (object) [
            'name' => $this->name,
            'description' => $this->description,
            'displayField' => $this->displayField,
            'sys' => $this->sys,
            'fields' => array_values($this->fields)
        ];
    }
}

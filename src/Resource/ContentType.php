<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Resource;

use Contentful\Core\Resource\ContentTypeInterface;
use Contentful\Delivery\Resource\ContentType\Field;
use Contentful\Delivery\SystemProperties\ContentType as SystemProperties;

/**
 * Content Types are schemas that define the fields of Entries. Every Entry can only contain values in the fields
 * defined by its Content Type, and the values of those fields must match the data type defined in the Content Type.
 */
class ContentType extends BaseResource implements ContentTypeInterface
{
    /**
     * @var SystemProperties
     */
    protected $sys;

    /**
     * Name of the Content Type.
     *
     * @var string
     */
    protected $name;

    /**
     * Description of the Content Type.
     *
     * @var string|null
     */
    protected $description;

    /**
     * The fields, keyed by ID.
     *
     * @var Field[]
     */
    protected $fields = [];

    /**
     * ID of main field used for display.
     *
     * @var string|null
     */
    protected $displayField;

    public function getSystemProperties(): SystemProperties
    {
        return $this->sys;
    }

    /**
     * Returns the space this content type belongs to.
     */
    public function getSpace(): Space
    {
        return $this->sys->getSpace();
    }

    /**
     * Returns the environment this content type belongs to.
     */
    public function getEnvironment(): Environment
    {
        return $this->sys->getEnvironment();
    }

    /**
     * Returns the name of this content type.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns all the fields of this content type as an associative array.
     * The key is the ID of the field.
     *
     * @return Field[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Returns the content type's description.
     *
     * @return string|null
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
     * @return Field|null
     */
    public function getField(string $fieldId, bool $tryCaseInsensitive = false)
    {
        if (isset($this->fields[$fieldId])) {
            return $this->fields[$fieldId];
        }

        if ($tryCaseInsensitive) {
            foreach ($this->fields as $name => $field) {
                if (mb_strtolower($name) === mb_strtolower($fieldId)) {
                    return $field;
                }
            }
        }

        return null;
    }

    /**
     * Returns the the display field of a content type. Commonly this is the title.
     *
     * Returns null if not display field is set.
     *
     * @return Field|null
     */
    public function getDisplayField()
    {
        if (null === $this->displayField) {
            return null;
        }

        return $this->getField($this->displayField);
    }

    /**
     * Adds a runtime field, of type unknown.
     */
    public function addUnknownField(string $name): Field
    {
        $this->fields[$name] = new Field($name, $name, 'Unknown');

        return $this->fields[$name];
    }

    public function jsonSerialize(): array
    {
        return [
            'sys' => $this->sys,
            'name' => $this->name,
            'description' => $this->description,
            'displayField' => $this->displayField,
            'fields' => array_values($this->fields),
        ];
    }
}

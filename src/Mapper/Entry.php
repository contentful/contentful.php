<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Mapper;

use Contentful\Core\Api\DateTimeImmutable;
use Contentful\Core\Api\Link;
use Contentful\Core\Api\Location;
use Contentful\Delivery\Resource\ContentType as ResourceContentType;
use Contentful\Delivery\Resource\ContentType\Field as ResourceContentTypeField;
use Contentful\Delivery\Resource\Entry as ResourceClass;

/**
 * Entry class.
 *
 * This class is responsible for converting raw API data into a PHP object
 * of class Contentful\Delivery\Resource\Entry.
 */
class Entry extends BaseMapper
{
    /**
     * {@inheritdoc}
     */
    public function map($resource, array $data)
    {
        $sys = $this->buildSystemProperties($data['sys']);
        $locale = $sys->getLocale();

        return $this->hydrate($resource ?: ResourceClass::class, [
            'sys' => $sys,
            'fields' => isset($data['fields'])
                ? $this->buildFields($sys->getContentType(), $data['fields'], $locale, $resource)
                : [],
        ]);
    }

    /**
     * @param ResourceContentType $contentType
     * @param array               $fields
     * @param string|null         $locale
     * @param ResourceClass|null  $previous
     *
     * @return array
     */
    private function buildFields(
        ResourceContentType $contentType,
        array $fields,
        $locale,
        ResourceClass $previous = \null
    ) {
        // We normalize the field data to always contain locales.
        foreach ($fields as $name => $data) {
            $fields[$name] = $locale ? [$locale => $data] : $data;
        }

        if ($previous) {
            $fields = $this->mergePreviousFields($fields, $previous);
        }

        $result = [];
        foreach ($fields as $name => $data) {
            $field = $contentType->getField($name);

            // If field is empty, it means that the data currently available
            // for the content type is not correct.
            // Instead of failing and causing a type error, we fallback on a simple
            // field, and leave the handling of those edge cases to the user.
            if (!$field) {
                @\trigger_error(\sprintf(
                    'Entry of content type "%s" ("%s") being built contains field "%s" which is not present in the content type definition.'
                    .' Please check your cache for stale content type definitions.',
                    $contentType->getName(),
                    $contentType->getId(),
                    $name
                ), \E_USER_WARNING);
                $field = $contentType->addUnknownField($name);
            }

            // If the field is empty (has no values for locales) we simply skip it;
            // the entry class will be able to properly return default values for those situations.
            if ($data) {
                $result[$name] = $this->buildField($field, $data);
            }
        }

        return $result;
    }

    /**
     * If a previous entry is given, this methods will combine all values from the $fields
     * array an those in the already built entry.
     * This is done so in the event of partial resource building (i.e. after having queried
     * the API using the "select" operator), the resulting object is increasingly updated,
     * therefore the new object will contain the biggest set of old plus new fields.
     *
     * @param array         $fields The field values that have been returned by the API
     * @param ResourceClass $entry  The previous entry object that was already built, if present
     *
     * @return array
     */
    private function mergePreviousFields(array $fields, ResourceClass $entry)
    {
        // Entry fields have private access, so we use this trick to fetch them.
        // https://ocramius.github.io/blog/accessing-private-php-class-members-without-reflection/
        $extractor = \Closure::bind(function (ResourceClass $entry) {
            return $entry->fields;
        }, \null, $entry);
        $currentFields = $extractor($entry);

        foreach ($fields as $name => $values) {
            if (!isset($currentFields[$name])) {
                $currentFields[$name] = [];
            }

            foreach ($values as $locale => $value) {
                $currentFields[$name][$locale] = $value;
            }
        }

        return $currentFields;
    }

    /**
     * @param ResourceContentTypeField $field
     * @param array                    $data
     *
     * @return array
     */
    private function buildField(ResourceContentTypeField $field, array $data)
    {
        $result = [];
        foreach ($data as $locale => $value) {
            $result[$locale] = $this->formatValue($field->getType(), $value, $field->getItemsType());
        }

        return $result;
    }

    /**
     * Transforms values from the original JSON representation to an appropriate PHP representation.
     *
     * @param string      $type
     * @param mixed       $value
     * @param string|null $itemsType The type of the items in the array, if it's an array field
     *
     * @return mixed
     */
    private function formatValue($type, $value, $itemsType = \null)
    {
        // Certain fields are already built as objects (Location, Link, DateTimeImmutable)
        // if the entry has already been built partially.
        // We restore these objects to their JSON implementations to avoid conflicts.
        if (\is_object($value) && $value instanceof \JsonSerializable) {
            $value = $value->jsonSerialize();
        }

        if (\null === $value) {
            return \null;
        }

        if ('Date' === $type) {
            return new DateTimeImmutable($value, new \DateTimeZone('UTC'));
        }

        if ('Location' === $type) {
            return new Location($value['lat'], $value['lon']);
        }

        if ('Link' === $type) {
            return new Link($value['sys']['id'], $value['sys']['linkType']);
        }

        if ('Array' === $type) {
            return \array_map(function ($value) use ($itemsType) {
                return $this->formatValue($itemsType, $value);
            }, $value);
        }

        return $value;
    }
}

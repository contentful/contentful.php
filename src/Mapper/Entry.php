<?php

/**
 * This file is part of the contentful.php package.
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
                ? $this->buildFields($sys->getContentType(), $data['fields'], $locale)
                : [],
        ]);
    }

    /**
     * @param ResourceContentType $contentType
     * @param array               $fields
     * @param string|null         $locale
     *
     * @return array
     */
    private function buildFields(ResourceContentType $contentType, array $fields, $locale)
    {
        $result = [];
        foreach ($fields as $name => $fieldData) {
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
                ), E_USER_WARNING);
                $field = $contentType->addUnknownField($name);
            }

            // If the field is empty (has no values for locales) we simply skip it;
            // the entry class will be able to properly return default values for those situations.
            if ($fieldData) {
                $data = $this->normalizeFieldData($fieldData, $locale);
                $result[$name] = $this->buildField($field, $data);
            }
        }

        return $result;
    }

    /**
     * @param ResourceContentTypeField $fieldConfig
     * @param array                    $fieldData
     *
     * @return array
     */
    private function buildField(ResourceContentTypeField $fieldConfig, array $fieldData)
    {
        $result = [];
        foreach ($fieldData as $locale => $value) {
            $result[$locale] = $this->formatValue($fieldConfig->getType(), $value, $fieldConfig->getItemsType());
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
    private function formatValue($type, $value, $itemsType = null)
    {
        if (null === $value) {
            return null;
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

<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Mapper;

use Contentful\Core\Api\DateTimeImmutable;
use Contentful\Core\Api\Link;
use Contentful\Core\Api\Location;
use Contentful\Delivery\Resource\ContentType as ResourceContentType;
use Contentful\Delivery\Resource\Entry as ResourceClass;
use Contentful\Delivery\SystemProperties\Entry as SystemProperties;

use function GuzzleHttp\json_decode as guzzle_json_decode;
use function GuzzleHttp\json_encode as guzzle_json_encode;

/**
 * Entry class.
 *
 * This class is responsible for converting raw API data into a PHP object
 * of class Contentful\Delivery\Resource\Entry.
 */
class Entry extends BaseTaggedMapper
{
    public function map($resource, array $data): ResourceClass
    {
        /** @var SystemProperties $sys */
        $sys = $this->createSystemProperties(SystemProperties::class, $data);
        $locale = $sys->getLocale();

        // We normalize the field data to always contain locales.
        foreach ($data['fields'] ?? [] as $name => $value) {
            // If the value is an empty array, and no locale was used,
            // we remove the value as the entry itself will handle default values.
            if (!$locale && [] === $value) {
                unset($data['fields'][$name]);
                continue;
            }

            $data['fields'][$name] = $locale ? [$locale => $value] : $value;
        }

        /** @var ResourceClass $entry */
        $entry = $this->hydrator->hydrate($resource ?: ResourceClass::class, [
            'sys' => $sys,
            'client' => $this->client,
            'fields' => isset($data['fields'])
                ? $this->buildFields($sys->getContentType(), $data['fields'], $resource)
                : [],
        ]);

        $entry->initLocales($entry->getSystemProperties()->getEnvironment()->getLocales());

        $tags = $this->createTags($data);
        $entry->initTags($tags);

        return $entry;
    }

    private function buildFields(
        ResourceContentType $contentType,
        array $fields,
        $previous = null
    ): array {
        if ($previous) {
            $fields = $this->mergePreviousFields($fields, $previous);
        }

        foreach ($fields as $name => $data) {
            $field = $contentType->getField($name);

            // If field is empty, it means that the data currently available
            // for the content type is not correct.
            // Instead of failing and causing a type error, we fallback on a simple
            // field, and leave the handling of those edge cases to the user.
            if (!$field) {
                @trigger_error(sprintf(
                    'Entry of content type "%s" ("%s") being built contains field "%s" which is not present in the content type definition.'
                    .' Please check your cache for stale content type definitions.',
                    $contentType->getName(),
                    $contentType->getId(),
                    $name
                ), \E_USER_WARNING);
                $field = $contentType->addUnknownField($name);
            }

            foreach ($data as $locale => $value) {
                $fields[$name][$locale] = $this->formatValue(
                    $field->getType(),
                    $value,
                    $field->getItemsType()
                );
            }
        }

        return $fields;
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
     */
    private function mergePreviousFields(array $fields, ResourceClass $entry): array
    {
        // Entry fields have private access, so we use this trick to fetch them.
        // https://ocramius.github.io/blog/accessing-private-php-class-members-without-reflection/
        $extractor = \Closure::bind(function (ResourceClass $entry) {
            return $entry->fields;
        }, null, $entry);
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
     * Transforms values from the original JSON representation to an appropriate PHP representation.
     *
     * @param string|null $itemsType The type of the items in the array, if it's an array field
     */
    private function formatValue(string $type, $value, ?string $itemsType = null)
    {
        // Certain fields are already built as objects (Location, Link, DateTimeImmutable)
        // if the entry has already been built partially.
        // We restore these objects to their JSON implementations to avoid conflicts.
        if (\is_object($value) && $value instanceof \JsonSerializable) {
            $value = guzzle_json_decode(guzzle_json_encode($value), true);
        }

        if (null === $value) {
            return null;
        }

        switch ($type) {
            case 'Array':
                return array_map(function ($value) use ($itemsType) {
                    return $this->formatValue((string) $itemsType, $value);
                }, $value);
            case 'Date':
                return new DateTimeImmutable($value, new \DateTimeZone('UTC'));
            case 'Link':
                return new Link($value['sys']['id'], $value['sys']['linkType']);
            case 'Location':
                return new Location($value['lat'], $value['lon']);
            case 'RichText':
                return $this->richTextParser->parse($value);
            default:
                return $value;
        }
    }
}

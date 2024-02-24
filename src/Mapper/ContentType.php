<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Mapper;

use Contentful\Delivery\Resource\ContentType as ResourceClass;
use Contentful\Delivery\Resource\ContentType\Field as ResourceContentTypeField;
use Contentful\Delivery\SystemProperties\ContentType as SystemProperties;

/**
 * ContentType class.
 *
 * This class is responsible for converting raw API data into a PHP object
 * of class Contentful\Delivery\Resource\ContentType.
 */
class ContentType extends BaseMapper
{
    public function map($resource, array $data): ResourceClass
    {
        $fields = [];
        foreach ($data['fields'] as $field) {
            $field = $this->mapField($field);
            $fields[$field->getId()] = $field;
        }

        /** @var ResourceClass $contentType */
        $contentType = $this->hydrator->hydrate($resource ?: ResourceClass::class, [
            'sys' => $this->createSystemProperties(SystemProperties::class, $data),
            'name' => $data['name'],
            'displayField' => $data['displayField'] ?? null,
            'description' => $data['description'] ?? null,
            'fields' => $fields,
        ]);

        return $contentType;
    }

    protected function mapField(array $data): ResourceContentTypeField
    {
        /** @var ResourceContentTypeField $field */
        $field = $this->hydrator->hydrate(ResourceContentTypeField::class, [
            'id' => $data['id'],
            'name' => $data['name'],
            'type' => $data['type'],
            'linkType' => $data['linkType'] ?? null,
            'itemsType' => isset($data['items']) && isset($data['items']['type']) ? $data['items']['type'] : null,
            'itemsLinkType' => isset($data['items']) && isset($data['items']['linkType']) ? $data['items']['linkType'] : null,
            'required' => $data['required'] ?? false,
            'localized' => $data['localized'] ?? false,
            'disabled' => $data['disabled'] ?? false,
        ]);

        return $field;
    }
}

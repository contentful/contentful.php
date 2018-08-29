<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Mapper;

use Contentful\Delivery\Resource\ContentType as ResourceClass;
use Contentful\Delivery\Resource\ContentType\Field as ResourceContentTypeField;

/**
 * ContentType class.
 *
 * This class is responsible for converting raw API data into a PHP object
 * of class Contentful\Delivery\Resource\ContentType.
 */
class ContentType extends BaseMapper
{
    /**
     * {@inheritdoc}
     */
    public function map($resource, array $data)
    {
        $fields = [];
        foreach ($data['fields'] as $field) {
            $field = $this->mapField($field);
            $fields[$field->getId()] = $field;
        }

        return $this->hydrate($resource ?: ResourceClass::class, [
            'sys' => $this->buildSystemProperties($data['sys']),
            'name' => $data['name'],
            'displayField' => isset($data['displayField']) ? $data['displayField'] : \null,
            'description' => isset($data['description']) ? $data['description'] : \null,
            'fields' => $fields,
        ]);
    }

    /**
     * @param array $data
     *
     * @return ResourceContentTypeField
     */
    protected function mapField(array $data)
    {
        return $this->builder->getMapper(__NAMESPACE__.'\\ContentType\\Field')
            ->map(\null, $data)
        ;
    }
}

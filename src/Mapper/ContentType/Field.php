<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Mapper\ContentType;

use Contentful\Delivery\Mapper\BaseMapper;
use Contentful\Delivery\Resource\ContentType\Field as ResourceClass;

/**
 * Field class.
 *
 * This class is responsible for converting raw API data into a PHP object
 * of class Contentful\Delivery\Resource\ContentType\Field.
 */
class Field extends BaseMapper
{
    /**
     * {@inheritdoc}
     */
    public function map($resource, array $data)
    {
        return $this->hydrate($resource ?: ResourceClass::class, [
            'id' => $data['id'],
            'name' => $data['name'],
            'type' => $data['type'],
            'linkType' => isset($data['linkType']) ? $data['linkType'] : \null,
            'itemsType' => isset($data['items']) && isset($data['items']['type']) ? $data['items']['type'] : \null,
            'itemsLinkType' => isset($data['items']) && isset($data['items']['linkType']) ? $data['items']['linkType'] : \null,
            'required' => isset($data['required']) ? $data['required'] : \false,
            'localized' => isset($data['localized']) ? $data['localized'] : \false,
            'disabled' => isset($data['disabled']) ? $data['disabled'] : \false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function injectClient()
    {
        return \false;
    }
}

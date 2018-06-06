<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Mapper;

use Contentful\Core\Resource\ResourceArray as ResourceClass;

/**
 * ResourceArray class.
 *
 * This class is responsible for converting raw API data into a PHP object
 * of class Contentful\ResourceArray.
 */
class ResourceArray extends BaseMapper
{
    /**
     * {@inheritdoc}
     */
    public function map($resource, array $data)
    {
        return new ResourceClass(
            \array_map(function ($item) {
                return $this->builder->build($item);
            }, $data['items']),
            $data['total'],
            $data['limit'],
            $data['skip']
        );
    }
}

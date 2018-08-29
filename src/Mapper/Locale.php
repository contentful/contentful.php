<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Mapper;

use Contentful\Delivery\Resource\Locale as ResourceClass;

/**
 * Locale class.
 *
 * This class is responsible for converting raw API data into a PHP object
 * of class Contentful\Delivery\Resource\Locale.
 */
class Locale extends BaseMapper
{
    /**
     * {@inheritdoc}
     */
    public function map($resource, array $data)
    {
        return $this->hydrate($resource ?: ResourceClass::class, [
            'sys' => $this->buildSystemProperties($data['sys']),
            'code' => $data['code'],
            'name' => $data['name'],
            'default' => $data['default'],
            'fallbackCode' => $data['fallbackCode'],
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

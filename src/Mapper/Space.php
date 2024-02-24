<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Mapper;

use Contentful\Delivery\Resource\Space as ResourceClass;
use Contentful\Delivery\SystemProperties\Space as SystemProperties;

/**
 * Space class.
 *
 * This class is responsible for converting raw API data into a PHP object
 * of class Contentful\Delivery\Resource\Space.
 */
class Space extends BaseMapper
{
    public function map($resource, array $data): ResourceClass
    {
        /** @var ResourceClass $space */
        $space = $this->hydrator->hydrate($resource ?: ResourceClass::class, [
            'sys' => $this->createSystemProperties(SystemProperties::class, $data),
            'name' => $data['name'],
        ]);

        return $space;
    }
}

<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Mapper;

use Contentful\Delivery\Resource\Locale as ResourceClass;
use Contentful\Delivery\SystemProperties\Locale as SystemProperties;

/**
 * Locale class.
 *
 * This class is responsible for converting raw API data into a PHP object
 * of class Contentful\Delivery\Resource\Locale.
 */
class Locale extends BaseMapper
{
    public function map($resource, array $data): ResourceClass
    {
        /** @var ResourceClass $locale */
        $locale = $this->hydrator->hydrate($resource ?: ResourceClass::class, [
            'sys' => $this->createSystemProperties(SystemProperties::class, $data),
            'code' => $data['code'],
            'name' => $data['name'],
            'default' => $data['default'],
            'fallbackCode' => $data['fallbackCode'],
        ]);

        return $locale;
    }
}

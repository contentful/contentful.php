<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Mapper;

use Contentful\Delivery\Resource\DeletedEntry as ResourceClass;
use Contentful\Delivery\SystemProperties\DeletedEntry as SystemProperties;

/**
 * DeletedEntry class.
 *
 * This class is responsible for converting raw API data into a PHP object
 * of class Contentful\Delivery\Resource\DeletedEntry.
 */
class DeletedEntry extends BaseMapper
{
    /**
     * {@inheritdoc}
     */
    public function map($resource, array $data): ResourceClass
    {
        /** @var ResourceClass $deletedEntry */
        $deletedEntry = $this->hydrator->hydrate($resource ?: ResourceClass::class, [
            'sys' => $this->createSystemProperties(SystemProperties::class, $data),
        ]);

        return $deletedEntry;
    }
}

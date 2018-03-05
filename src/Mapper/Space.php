<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Mapper;

use Contentful\Delivery\Resource\Locale as LocaleResourceClass;
use Contentful\Delivery\Resource\Space as ResourceClass;

/**
 * Space class.
 *
 * This class is responsible for converting raw API data into a PHP object
 * of class Contentful\Delivery\Resource\Space.
 */
class Space extends BaseMapper
{
    /**
     * {@inheritdoc}
     */
    public function map($resource, array $data)
    {
        return $this->hydrate($resource ?: ResourceClass::class, [
            'sys' => $this->buildSystemProperties($data['sys']),
            'name' => $data['name'],
            'locales' => \array_map([$this, 'buildLocale'], $data['locales']),
        ]);
    }

    /**
     * @param array $locale
     *
     * @return LocaleResourceClass[]
     */
    protected function buildLocale(array $locale)
    {
        return $this->builder->getMapper(Locale::class)
            ->map(null, $locale);
    }
}

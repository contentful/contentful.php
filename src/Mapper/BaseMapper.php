<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Mapper;

use Contentful\Core\Resource\ResourceArray;
use Contentful\Core\Resource\ResourceInterface;
use Contentful\Core\ResourceBuilder\MapperInterface;
use Contentful\Core\ResourceBuilder\ObjectHydrator;
use Contentful\Delivery\Client;
use Contentful\Delivery\Resource\LocalizedResource;
use Contentful\Delivery\ResourceBuilder;
use Contentful\Delivery\SystemProperties;

/**
 * BaseMapper class.
 */
abstract class BaseMapper implements MapperInterface
{
    /**
     * @var ResourceBuilder
     */
    protected $builder;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ObjectHydrator
     */
    protected $hydrator;

    /**
     * BaseMapper constructor.
     *
     * @param ResourceBuilder $builder
     * @param Client          $client
     */
    public function __construct(ResourceBuilder $builder, Client $client)
    {
        $this->builder = $builder;
        $this->client = $client;
        $this->hydrator = new ObjectHydrator();
    }

    /**
     * @param string|object $target either a FQCN, or an object whose class will be automatically inferred
     * @param array         $data
     *
     * @return ResourceInterface|ResourceArray
     */
    protected function hydrate($target, array $data)
    {
        if ($this->injectClient()) {
            $data['client'] = $this->client;
        }

        $target = $this->hydrator->hydrate($target, $data);

        if ($target instanceof LocalizedResource) {
            $locales = $this->client->getEnvironment()->getLocales();
            $target->initLocales($locales);
        }

        return $target;
    }

    /**
     * @param array $sys
     *
     * @return SystemProperties
     */
    protected function buildSystemProperties(array $sys)
    {
        $sys['__client'] = $this->client;

        return new SystemProperties($sys);
    }

    /**
     * @param mixed       $fieldData
     * @param string|null $locale
     *
     * @return array
     */
    protected function normalizeFieldData($fieldData, $locale)
    {
        if (!$locale) {
            return $fieldData;
        }

        return [$locale => $fieldData];
    }

    /**
     * Override this method for blocking the mapper from injecting the client property.
     *
     * @return bool
     */
    protected function injectClient()
    {
        return \true;
    }
}

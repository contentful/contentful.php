<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Resource;

use Contentful\Core\Api\Link;
use Contentful\Core\Resource\ResourceInterface;
use Contentful\Delivery\Client;
use Contentful\Delivery\SystemProperties;

abstract class BaseResource implements ResourceInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var SystemProperties
     */
    protected $sys;

    /**
     * Resources in this SDK should not be built using `$new Class()`.
     * This method is only useful in testing environments, where the resource
     * needs to be subclasses and this method made public.
     *
     * @param array $data
     */
    protected function __construct(array $data)
    {
        foreach ($data as $property => $value) {
            if (\property_exists($this, $property)) {
                $this->$property = $value;
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return SystemProperties
     */
    public function getSystemProperties()
    {
        return $this->sys;
    }

    /**
     * {@inheritdoc}
     */
    public function asLink()
    {
        return new Link(
            $this->sys->getId(),
            $this->sys->getType()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->sys->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->sys->getType();
    }
}

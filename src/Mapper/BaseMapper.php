<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2019 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Mapper;

use Contentful\Core\Resource\ResourceInterface;
use Contentful\Core\Resource\SystemPropertiesInterface;
use Contentful\Core\ResourceBuilder\MapperInterface;
use Contentful\Core\ResourceBuilder\ObjectHydrator;
use Contentful\Core\ResourceBuilder\ResourceBuilderInterface;
use Contentful\Delivery\Client\ClientInterface;
use Contentful\RichText\ParserInterface;

/**
 * BaseMapper class.
 */
abstract class BaseMapper implements MapperInterface
{
    /**
     * @var ResourceBuilderInterface
     */
    protected $builder;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var ParserInterface
     */
    protected $richTextParser;

    /**
     * @var ObjectHydrator
     */
    protected $hydrator;

    /**
     * BaseMapper constructor.
     *
     * @param ResourceBuilderInterface $builder
     * @param ClientInterface          $client
     * @param ParserInterface          $richTextParser
     */
    public function __construct(ResourceBuilderInterface $builder, ClientInterface $client, ParserInterface $richTextParser)
    {
        $this->builder = $builder;
        $this->client = $client;
        $this->richTextParser = $richTextParser;
        $this->hydrator = new ObjectHydrator();
    }

    /**
     * @param string $class
     * @param array  $data
     *
     * @return SystemPropertiesInterface
     */
    protected function createSystemProperties(string $class, array $data): SystemPropertiesInterface
    {
        $sys = $data['sys'];

        if (isset($sys['space']) && !$sys['space'] instanceof ResourceInterface) {
            $sys['space'] = $this->client->getSpace();
        }

        if (isset($sys['environment']) && !$sys['environment'] instanceof ResourceInterface) {
            $sys['environment'] = $this->client->getEnvironment();
        }

        if (isset($sys['contentType']) && !$sys['contentType'] instanceof ResourceInterface) {
            $sys['contentType'] = $this->client->getContentType($sys['contentType']['sys']['id']);
        }

        return new $class($sys);
    }

    /**
     * @param mixed       $fieldData
     * @param string|null $locale
     *
     * @return array
     */
    protected function normalizeFieldData($fieldData, string $locale = \null)
    {
        if (!$locale) {
            return $fieldData;
        }

        return [$locale => $fieldData];
    }
}

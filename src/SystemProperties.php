<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery;

use Contentful\Core\Api\DateTimeImmutable;
use Contentful\Core\Api\Link;
use Contentful\Core\Resource\ResourceInterface;
use Contentful\Core\Resource\SystemPropertiesInterface;
use Contentful\Delivery\Resource\ContentType;
use Contentful\Delivery\Resource\Environment;
use Contentful\Delivery\Resource\Space;

/**
 * A SystemProperties instance contains the metadata of a resource.
 */
class SystemProperties implements SystemPropertiesInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var Link|Space|null
     */
    private $space;

    /**
     * @var Link|ContentType|null
     */
    private $contentType;

    /**
     * @var Link|Environment|null
     */
    private $environment;

    /**
     * @var int|null
     */
    private $revision;

    /**
     * @var string|null
     */
    private $locale;

    /**
     * @var DateTimeImmutable|null
     */
    private $createdAt;

    /**
     * @var DateTimeImmutable|null
     */
    private $updatedAt;

    /**
     * @var DateTimeImmutable|null
     */
    private $deletedAt;

    /**
     * @var Client|null
     */
    private $client;

    /**
     * SystemProperties constructor.
     *
     * @param array $sys
     */
    public function __construct(array $sys)
    {
        $this->client = $sys['__client'] ?? \null;

        $this->id = $sys['id'] ?? \null;
        $this->type = $sys['type'] ?? \null;
        $this->revision = $sys['revision'] ?? \null;
        $this->locale = $sys['locale'] ?? \null;

        $this->space = $this->checkAndBuildResource($sys, 'space');
        $this->contentType = $this->checkAndBuildResource($sys, 'contentType');
        $this->environment = $this->checkAndBuildResource($sys, 'environment');

        $this->createdAt = $this->checkAndBuildDate($sys, 'createdAt');
        $this->updatedAt = $this->checkAndBuildDate($sys, 'updatedAt');
        $this->deletedAt = $this->checkAndBuildDate($sys, 'deletedAt');
    }

    /**
     * Utility function for building internal properties that link to resources.
     *
     * @param array  $sys
     * @param string $name
     *
     * @return Link|ResourceInterface|null
     */
    private function checkAndBuildResource(array $sys, $name)
    {
        if (!isset($sys[$name])) {
            return \null;
        }

        // The system properties might already contain built resource objects,
        // so before creating a Link, we check whether the value is already a built object.
        if ($sys[$name] instanceof ResourceInterface) {
            return $sys[$name];
        }

        return new Link(
            $sys[$name]['sys']['id'],
            $sys[$name]['sys']['linkType']
        );
    }

    /**
     * Utility function for building internal properties that are dates.
     *
     * @param array  $sys
     * @param string $name
     *
     * @return DateTimeImmutable|null
     */
    private function checkAndBuildDate(array $sys, $name)
    {
        if (!isset($sys[$name])) {
            return \null;
        }

        return new DateTimeImmutable($sys[$name]);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return Space|null
     */
    public function getSpace()
    {
        if ($this->space instanceof Link) {
            $this->space = $this->client->resolveLink($this->space);
        }

        return $this->space;
    }

    /**
     * @return ContentType|null
     */
    public function getContentType()
    {
        if ($this->contentType instanceof Link) {
            $this->contentType = $this->client->resolveLink($this->contentType);
        }

        return $this->contentType;
    }

    /**
     * @return Environment|null
     */
    public function getEnvironment()
    {
        if ($this->environment instanceof Link) {
            $this->environment = $this->client->resolveLink($this->environment);
        }

        return $this->environment;
    }

    /**
     * @return int|null
     */
    public function getRevision()
    {
        return $this->revision;
    }

    /**
     * @return string|null
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return \array_filter([
            'id' => $this->id,
            'type' => $this->type,
            'space' => $this->space instanceof ResourceInterface
                ? $this->space->asLink()
                : $this->space,
            'contentType' => $this->contentType instanceof ResourceInterface
                ? $this->contentType->asLink()
                : $this->contentType,
            'environment' => $this->environment instanceof ResourceInterface
                ? $this->environment->asLink()
                : $this->environment,
            'revision' => $this->revision,
            'locale' => $this->locale,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ]);
    }
}

<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

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
        $this->client = isset($sys['__client']) ? $sys['__client'] : null;

        $this->id = isset($sys['id']) ? $sys['id'] : null;
        $this->type = isset($sys['type']) ? $sys['type'] : null;

        $this->space = $this->checkAndBuildResource($sys, 'space');
        $this->contentType = $this->checkAndBuildResource($sys, 'contentType');
        $this->environment = $this->checkAndBuildResource($sys, 'environment');

        $this->revision = isset($sys['revision']) ? $sys['revision'] : null;

        $this->createdAt = isset($sys['createdAt']) ? new DateTimeImmutable($sys['createdAt']) : null;
        $this->updatedAt = isset($sys['updatedAt']) ? new DateTimeImmutable($sys['updatedAt']) : null;
        $this->deletedAt = isset($sys['deletedAt']) ? new DateTimeImmutable($sys['deletedAt']) : null;

        $this->locale = isset($sys['locale']) ? $sys['locale'] : null;
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
            return null;
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
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
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
        $sys = [
            'id' => $this->id,
            'type' => $this->type,
        ];

        if (null !== $this->space) {
            $sys['space'] = [
                'sys' => [
                    'type' => 'Link',
                    'linkType' => 'Space',
                    'id' => $this->space->getId(),
                ],
            ];
        }
        if (null !== $this->contentType) {
            $sys['contentType'] = [
                'sys' => [
                    'type' => 'Link',
                    'linkType' => 'ContentType',
                    'id' => $this->contentType->getId(),
                ],
            ];
        }
        if (null !== $this->environment) {
            $sys['environment'] = [
                'sys' => [
                    'type' => 'Link',
                    'linkType' => 'Environment',
                    'id' => $this->environment->getId(),
                ],
            ];
        }

        if (null !== $this->revision) {
            $sys['revision'] = $this->revision;
        }
        if (null !== $this->locale) {
            $sys['locale'] = $this->locale;
        }

        if (null !== $this->createdAt) {
            $sys['createdAt'] = (string) $this->createdAt;
        }
        if (null !== $this->updatedAt) {
            $sys['updatedAt'] = (string) $this->updatedAt;
        }
        if (null !== $this->deletedAt) {
            $sys['deletedAt'] = (string) $this->deletedAt;
        }

        return $sys;
    }
}

<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

use Contentful\Core\Api\DateTimeImmutable;
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
     * @var Space|null
     */
    private $space;

    /**
     * @var ContentType|null
     */
    private $contentType;

    /**
     * @var Environment|null
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
     * SystemProperties constructor.
     *
     * @param array $sys
     */
    public function __construct(array $sys)
    {
        $this->id = isset($sys['id']) ? $sys['id'] : null;
        $this->type = isset($sys['type']) ? $sys['type'] : null;

        $this->space = isset($sys['space']) ? $sys['space'] : null;
        $this->contentType = isset($sys['contentType']) ? $sys['contentType'] : null;
        $this->environment = isset($sys['environment']) ? $sys['environment'] : null;

        $this->revision = isset($sys['revision']) ? $sys['revision'] : null;

        $this->createdAt = isset($sys['createdAt']) ? new DateTimeImmutable($sys['createdAt']) : null;
        $this->updatedAt = isset($sys['updatedAt']) ? new DateTimeImmutable($sys['updatedAt']) : null;
        $this->deletedAt = isset($sys['deletedAt']) ? new DateTimeImmutable($sys['deletedAt']) : null;

        $this->locale = isset($sys['locale']) ? $sys['locale'] : null;
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
        return $this->space;
    }

    /**
     * @return ContentType|null
     */
    public function getContentType()
    {
        return $this->contentType;
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
     * @return Environment|null
     */
    public function getEnvironment()
    {
        return $this->environment;
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

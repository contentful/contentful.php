<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Resource;

use Contentful\Core\Api\DateTimeImmutable;

/**
 * A DeletedResource encodes metadata about a deleted resource.
 */
abstract class DeletedResource extends BaseResource
{
    /**
     * Returns the last revision of the resource before it was deleted.
     *
     * @return int|null
     */
    public function getRevision()
    {
        return $this->sys->getRevision();
    }

    /**
     * Returns the time when the resource was updated.
     *
     * @return DateTimeImmutable|null
     */
    public function getUpdatedAt()
    {
        return $this->sys->getUpdatedAt();
    }

    /**
     * Returns the time when the resource was created.
     *
     * @return DateTimeImmutable|null
     */
    public function getCreatedAt()
    {
        return $this->sys->getCreatedAt();
    }

    /**
     * Returns the time when the resource was deleted.
     *
     * @return DateTimeImmutable|null
     */
    public function getDeletedAt()
    {
        return $this->sys->getDeletedAt();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'sys' => $this->sys,
        ];
    }
}

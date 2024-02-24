<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Synchronization;

use Contentful\Delivery\Resource\ContentType;

/**
 * A sync Query can be used to limit what type of resources and events should be synced.
 *
 * @see Mananager Synchronization\Mananager
 */
class Query
{
    /**
     * Limit the sync to event to a specific type.
     *
     * @var string|null
     */
    private $type = 'all';

    /**
     * For entries, limit results to this content type.
     *
     * @var string|null
     */
    private $contentType;

    /**
     * Returns the parameters to execute this query.
     */
    public function getQueryData(): array
    {
        $data = [
            'initial' => 'true',
            'type' => 'all' !== $this->type ? $this->type : null,
            'content_type' => $this->contentType,
        ];

        return $data;
    }

    /**
     * The urlencoded query string for this query.
     */
    public function getQueryString(): string
    {
        return http_build_query($this->getQueryData(), '', '&', \PHP_QUERY_RFC3986);
    }

    /**
     * Set the Type of event or resource to sync.
     *
     * Set to null to sync everything.
     *
     * Valid values for $type are:
     *  - all
     *  - Asset
     *  - Entry
     *  - Deletion
     *  - DeletedAsset
     *  - DeletedEntry
     *
     * @throws \InvalidArgumentException when an invalid $type is set
     *
     * @return $this
     */
    public function setType(?string $type = null)
    {
        $validTypes = ['all', 'Asset', 'Entry', 'Deletion', 'DeletedAsset', 'DeletedEntry'];
        if (!\in_array($type, $validTypes, true)) {
            throw new \InvalidArgumentException(sprintf('Unexpected type "%s".', $type));
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Set the content type to which results should be limited. Set to NULL to not filter for a content type.
     *
     * @param ContentType|string|null $contentType
     *
     * @return $this
     */
    public function setContentType($contentType)
    {
        if ($contentType instanceof ContentType) {
            $contentType = $contentType->getId();
        }

        $this->contentType = $contentType;

        $this->setType('Entry');

        return $this;
    }
}

<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Synchronization;

use Contentful\Delivery\ContentType;

/**
 * A sync Query can be used to limit what type of resources and events should be synced
 *
 * @see \Contentful\Delivery\Synchronization\Mananager Synchronization\Mananager
 * @api
 */
class Query
{
    /**
     * Limit the sync to event to a specific type
     *
     * @var string
     */
    private $type = 'all';

    /**
     * For entries, limit results to this content type
     *
     * @var string|null
     */
    private $contentType;

    /**
     * Query constructor.
     *
     * Empty for now, included for forward compatibility.
     */
    public function __construct()
    {
    }

    /**
     * Returns the parameters to execute this query.
     *
     * @return array
     *
     * @api
     */
    public function getQueryData()
    {
        $data = [
            'initial' => true,
            'type' => $this->type !== 'all' ? $this->type : null,
            'content_type' => $this->contentType
        ];

        return $data;
    }

    /**
     * The urlencoded query string for this query.
     *
     * @return string
     *
     * @internal
     */
    public function getQueryString()
    {
        return http_build_query($this->getQueryData(), '', '&', PHP_QUERY_RFC3986);
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
     * @param  string|null $type
     *
     * @return $this
     *
     * @throws \InvalidArgumentException When an invalid $type is set.
     *
     * @api
     */
    public function setType($type)
    {
        $validTypes = ['all', 'Asset', 'Entry', 'Deletion', 'DeletedAsset', 'DeletedEntry'];
        if (!in_array($type, $validTypes)) {
            throw new \InvalidArgumentException('Unexpected type ' . $type);
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Set the content type to which results should be limited. Set to NULL to not filter for a content type.
     *
     * @param  ContentType|string|null $contentType
     *
     * @return $this
     *
     * @api
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

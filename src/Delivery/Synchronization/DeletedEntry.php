<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Synchronization;

/**
 * A DeletedEntry describes an entry that has been deleted.
 *
 * @api
 */
class DeletedEntry extends DeletedResource
{
    /**
     * This method always returns null when used with the sync API. It does return a value when parsing a webhook response.
     *
     * @return \Contentful\Delivery\ContentType|null
     */
    public function getContentType()
    {
        return $this->sys->getContentType();
    }
}

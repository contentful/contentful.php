<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Synchronization;

use Contentful\Delivery\Resource\ContentType;

/**
 * A DeletedEntry describes an entry that has been deleted.
 */
class DeletedEntry extends DeletedResource
{
    /**
     * This method always returns null when used with the sync API. It does return a value when parsing a webhook response.
     *
     * @return ContentType|null
     */
    public function getContentType()
    {
        return $this->sys->getContentType();
    }
}

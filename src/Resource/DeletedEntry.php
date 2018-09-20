<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Resource;

/**
 * A DeletedEntry describes an entry that has been deleted.
 */
class DeletedEntry extends DeletedResource
{
    /**
     * This method always returns null when used with the sync API.
     * It does return a value when parsing a webhook response.
     *
     * @return ContentType|null
     */
    public function getContentType()
    {
        return $this->sys->getContentType();
    }
}

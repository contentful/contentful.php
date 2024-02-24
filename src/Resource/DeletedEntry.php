<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Resource;

use Contentful\Delivery\SystemProperties\DeletedEntry as SystemProperties;

/**
 * A DeletedEntry describes an entry that has been deleted.
 */
class DeletedEntry extends DeletedResource
{
    /**
     * @var SystemProperties
     */
    protected $sys;

    public function getSystemProperties(): SystemProperties
    {
        return $this->sys;
    }

    /**
     * This method always returns null when used with the sync API.
     * It does return a value when parsing a webhook response.
     */
    public function getContentType(): ContentType
    {
        return $this->sys->getContentType();
    }
}

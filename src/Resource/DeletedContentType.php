<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Resource;

use Contentful\Delivery\SystemProperties\DeletedContentType as SystemProperties;

/**
 * A DeletedContentType describes a content type that has been deleted.
 */
class DeletedContentType extends DeletedResource
{
    /**
     * @var SystemProperties
     */
    protected $sys;

    public function getSystemProperties(): SystemProperties
    {
        return $this->sys;
    }
}

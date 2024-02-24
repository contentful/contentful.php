<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Resource;

use Contentful\Delivery\SystemProperties\DeletedAsset as SystemProperties;

/**
 * A DeletedAsset describes an asset that has been deleted.
 */
class DeletedAsset extends DeletedResource
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

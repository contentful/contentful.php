<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\SystemProperties\Component;

use Contentful\Delivery\Resource\Space;

trait SpaceTrait
{
    /**
     * @var Space
     */
    protected $space;

    /**
     * @return Space
     */
    public function getSpace(): Space
    {
        return $this->space;
    }
}

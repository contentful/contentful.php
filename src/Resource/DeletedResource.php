<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Resource;

/**
 * A DeletedResource encodes metadata about a deleted resource.
 */
abstract class DeletedResource extends BaseResource
{
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

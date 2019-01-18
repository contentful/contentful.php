<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2019 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

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
            'sys' => $this->getSystemProperties(),
        ];
    }
}

<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Resource;

use Contentful\Delivery\SystemProperties\Space as SystemProperties;

/**
 * The Space class represents a single space identified by its ID and holding some metadata.
 */
class Space extends BaseResource
{
    /**
     * @var SystemProperties
     */
    protected $sys;

    /**
     * @var string
     */
    protected $name;

    public function getSystemProperties(): SystemProperties
    {
        return $this->sys;
    }

    /**
     * Returns the name of this space.
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function jsonSerialize(): array
    {
        return [
            'sys' => $this->sys,
            'name' => $this->name,
        ];
    }
}

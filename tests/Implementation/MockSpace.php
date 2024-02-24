<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Implementation;

use Contentful\Delivery\Resource\Space;
use Contentful\Delivery\SystemProperties\Space as SystemProperties;

class MockSpace extends Space
{
    /**
     * MockSpace constructor.
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    public static function withSys(string $id = 'spaceId', array $data = []): self
    {
        return new static(array_merge($data, [
            'sys' => new SystemProperties([
                'id' => $id,
                'type' => 'Space',
            ]),
        ]));
    }
}

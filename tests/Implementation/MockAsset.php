<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Implementation;

use Contentful\Delivery\Resource\Asset;
use Contentful\Delivery\SystemProperties\Asset as SystemProperties;

class MockAsset extends Asset
{
    /**
     * MockAsset constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    /**
     * @param string      $id
     * @param array       $data
     * @param string|null $locale
     *
     * @return MockAsset
     */
    public static function withSys(string $id = 'assetId', array $data = [], string $locale = \null): self
    {
        return new static(\array_merge($data, [
            'sys' => new SystemProperties([
                'id' => $id,
                'type' => 'Asset',
                'space' => MockSpace::withSys('spaceId'),
                'environment' => MockEnvironment::withSys('environmentId'),
                'revision' => 1,
                'createdAt' => '2010-01-01T12:00:00.123Z',
                'updatedAt' => '2010-01-01T12:00:00.123Z',
                'locale' => $locale,
            ]),
        ]));
    }
}

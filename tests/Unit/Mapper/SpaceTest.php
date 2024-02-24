<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Unit\Mapper;

use Contentful\Delivery\Mapper\Space as Mapper;
use Contentful\Delivery\Resource\Space;
use Contentful\Tests\Delivery\Implementation\MockClient;
use Contentful\Tests\Delivery\Implementation\MockParser;
use Contentful\Tests\Delivery\Implementation\MockResourceBuilder;
use Contentful\Tests\Delivery\TestCase;

class SpaceTest extends TestCase
{
    public function testMapper()
    {
        $mapper = new Mapper(
            new MockResourceBuilder(),
            new MockClient(),
            new MockParser()
        );

        /** @var Space $resource */
        $resource = $mapper->map(null, [
            'sys' => [
                'id' => 'spaceId',
                'type' => 'Space',
            ],
            'name' => 'My special space',
        ]);

        $this->assertInstanceOf(Space::class, $resource);
        $this->assertSame('spaceId', $resource->getId());
        $this->assertSame('Space', $resource->getType());

        $this->assertSame('My special space', $resource->getName());
    }
}

<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Unit\Mapper;

use Contentful\Core\Resource\ResourceArray;
use Contentful\Delivery\Mapper\ResourceArray as Mapper;
use Contentful\Delivery\Resource\Entry;
use Contentful\Tests\Delivery\Implementation\MockClient;
use Contentful\Tests\Delivery\Implementation\MockParser;
use Contentful\Tests\Delivery\Implementation\MockResourceBuilder;
use Contentful\Tests\Delivery\TestCase;

class ResourceArrayTest extends TestCase
{
    public function testMapper()
    {
        $mapper = new Mapper(
            new MockResourceBuilder(),
            new MockClient(),
            new MockParser()
        );

        /** @var ResourceArray $resource */
        $resource = $mapper->map(null, [
            'sys' => [
                'type' => 'Array',
            ],
            'items' => [
                [],
            ],
            'total' => 1000,
            'skip' => 50,
            'limit' => 100,
        ]);

        $this->assertInstanceOf(ResourceArray::class, $resource);
        $this->assertContainsOnlyInstancesOf(Entry::class, $resource->getItems());
        $this->assertCount(1, $resource->getItems());
        $this->assertSame(1000, $resource->getTotal());
        $this->assertSame(50, $resource->getSkip());
        $this->assertSame(100, $resource->getLimit());
    }
}

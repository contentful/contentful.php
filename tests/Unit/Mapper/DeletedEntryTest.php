<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Unit\Mapper;

use Contentful\Delivery\Mapper\DeletedEntry as Mapper;
use Contentful\Delivery\Resource\DeletedEntry;
use Contentful\Tests\Delivery\Implementation\MockClient;
use Contentful\Tests\Delivery\Implementation\MockEnvironment;
use Contentful\Tests\Delivery\Implementation\MockParser;
use Contentful\Tests\Delivery\Implementation\MockResourceBuilder;
use Contentful\Tests\Delivery\Implementation\MockSpace;
use Contentful\Tests\Delivery\TestCase;

class DeletedEntryTest extends TestCase
{
    public function testMapper()
    {
        $mapper = new Mapper(
            new MockResourceBuilder(),
            new MockClient(),
            new MockParser()
        );

        $space = MockSpace::withSys('spaceId');
        $environment = MockEnvironment::withSys('environmentId');

        /** @var DeletedEntry $resource */
        $resource = $mapper->map(null, [
            'sys' => [
                'id' => 'deletedEntryId',
                'type' => 'DeletedEntry',
                'space' => $space,
                'environment' => $environment,
                'revision' => 1,
                'createdAt' => '2016-01-01T12:00:00.123Z',
                'updatedAt' => '2017-01-01T12:00:00.123Z',
                'deletedAt' => '2018-01-01T12:00:00.123Z',
            ],
        ]);

        $this->assertInstanceOf(DeletedEntry::class, $resource);
        $this->assertSame('deletedEntryId', $resource->getId());
        $this->assertSame('DeletedEntry', $resource->getType());

        $sys = $resource->getSystemProperties();
        $this->assertSame($space, $sys->getSpace());
        $this->assertSame($environment, $sys->getEnvironment());
        $this->assertSame(1, $sys->getRevision());
        $this->assertSame('2016-01-01T12:00:00.123Z', (string) $sys->getCreatedAt());
        $this->assertSame('2017-01-01T12:00:00.123Z', (string) $sys->getUpdatedAt());
        $this->assertSame('2018-01-01T12:00:00.123Z', (string) $sys->getDeletedAt());

        $contentType = $sys->getContentType();
        $this->assertSame('__DeletedEntryContentType', $contentType->getId());
        $this->assertSame(1, $contentType->getSystemProperties()->getRevision());
        $this->assertSame(
            '2015-01-01T12:00:00Z',
            (string) $contentType->getSystemProperties()->getCreatedAt()
        );
        $this->assertSame(
            '2015-01-01T12:00:00Z',
            (string) $contentType->getSystemProperties()->getUpdatedAt()
        );
    }
}

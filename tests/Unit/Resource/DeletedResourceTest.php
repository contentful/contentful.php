<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery\Unit\Resource;

use Contentful\Core\Api\DateTimeImmutable;
use Contentful\Delivery\Resource\ContentType;
use Contentful\Delivery\Resource\DeletedEntry;
use Contentful\Delivery\Resource\DeletedResource;
use Contentful\Delivery\Resource\Space;
use Contentful\Delivery\SystemProperties;
use Contentful\Tests\Delivery\TestCase;

class DeletedResourceTest extends TestCase
{
    public function testGetter()
    {
        $space = $this->getMockBuilder(Space::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resource = new ConcreteDeletedResource(new SystemProperties(
            '4rPdazIwWkuuKEAQgemSmO',
            'DeletedEntry',
            $space,
            null,
            1,
            $createdAt = new DateTimeImmutable('2014-08-11T08:30:42.559Z'),
            $updatedAt = new DateTimeImmutable('2014-08-12T08:30:42.559Z'),
            $deletedAt = new DateTimeImmutable('2014-08-13T08:30:42.559Z')
        ));

        $this->assertSame('4rPdazIwWkuuKEAQgemSmO', $resource->getId());
        $this->assertSame($space, $resource->getSpace());
        $this->assertSame(1, $resource->getRevision());
        $this->assertSame($createdAt, $resource->getCreatedAt());
        $this->assertSame($updatedAt, $resource->getUpdatedAt());
        $this->assertSame($deletedAt, $resource->getDeletedAt());
    }

    public function testContentTypeDeletedEntry()
    {
        $space = $this->getMockBuilder(Space::class)
            ->disableOriginalConstructor()
            ->getMock();

        $deletedEntry = new DeletedEntry(new SystemProperties(
            '4rPdazIwWkuuKEAQgemSmO',
            'DeletedEntry',
            $space,
            null,
            1,
            new DateTimeImmutable('2014-08-11T08:30:42.559Z'),
            new DateTimeImmutable('2014-08-12T08:30:42.559Z'),
            new DateTimeImmutable('2014-08-13T08:30:42.559Z')
        ));

        $this->assertNull($deletedEntry->getContentType());

        $ct = $this->getMockBuilder(ContentType::class)
            ->disableOriginalConstructor()
            ->getMock();

        $deletedEntry = new DeletedEntry(new SystemProperties(
            '4rPdazIwWkuuKEAQgemSmO',
            'DeletedEntry',
            $space,
            $ct,
            1,
            new DateTimeImmutable('2014-08-11T08:30:42.559Z'),
            new DateTimeImmutable('2014-08-12T08:30:42.559Z'),
            new DateTimeImmutable('2014-08-13T08:30:42.559Z')
        ));

        $this->assertSame($ct, $deletedEntry->getContentType());
    }

    public function testJsonSerialize()
    {
        $space = $this->getMockBuilder(Space::class)
            ->disableOriginalConstructor()
            ->getMock();

        $space->method('getId')
            ->willReturn('cfexampleapi');

        $resource = new ConcreteDeletedResource(new SystemProperties(
            '4rPdazIwWkuuKEAQgemSmO',
            'DeletedEntry',
            $space,
            null,
            1,
            new DateTimeImmutable('2014-08-11T08:30:42.559Z'),
            new DateTimeImmutable('2014-08-12T08:30:42.559Z'),
            new DateTimeImmutable('2014-08-13T08:30:42.559Z')
        ));

        $this->assertJsonFixtureEqualsJsonObject('serialize.json', $resource);
    }
}

class ConcreteDeletedResource extends DeletedResource
{
}

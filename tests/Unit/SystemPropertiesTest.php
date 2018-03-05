<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery\Unit;

use Contentful\Core\Api\DateTimeImmutable;
use Contentful\Delivery\Resource\ContentType;
use Contentful\Delivery\Resource\Space;
use Contentful\Delivery\SystemProperties;
use Contentful\Tests\Delivery\TestCase;

class SystemPropertiesTest extends TestCase
{
    public function testGetter()
    {
        $space = $this->getMockBuilder(Space::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contentType = $this->getMockBuilder(ContentType::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sys = new SystemProperties(
            '123',
            'Type',
            $space,
            $contentType,
            1,
            $createdAt = new DateTimeImmutable('2014-08-11T08:30:42.559Z'),
            $updatedAt = new DateTimeImmutable('2014-08-12T08:30:42.559Z'),
            $deletedAt = new DateTimeImmutable('2014-08-13T08:30:42.559Z')
        );

        $this->assertSame('123', $sys->getId());
        $this->assertSame('Type', $sys->getType());
        $this->assertSame($space, $sys->getSpace());
        $this->assertSame($contentType, $sys->getContentType());
        $this->assertSame(1, $sys->getRevision());
        $this->assertSame($createdAt, $sys->getCreatedAt());
        $this->assertSame($updatedAt, $sys->getUpdatedAt());
        $this->assertSame($deletedAt, $sys->getDeletedAt());
    }

    public function testJsonSerializeSpace()
    {
        $sys = new SystemProperties('123', 'Space');

        $this->assertJsonFixtureEqualsJsonObject('serialize_space.json', $sys);
    }

    public function testJsonSerializeDeletedResource()
    {
        $space = $this->getMockBuilder(Space::class)
            ->disableOriginalConstructor()
            ->getMock();

        $space->method('getId')
            ->willReturn('cfexampleapi');

        $resource = new SystemProperties(
            '4rPdazIwWkuuKEAQgemSmO',
            'DeletedEntry',
            $space,
            null,
            1,
            new DateTimeImmutable('2014-08-11T08:30:42.559Z'),
            new DateTimeImmutable('2014-08-12T08:30:42.559Z'),
            new DateTimeImmutable('2014-08-13T08:30:42.559Z')
        );

        $this->assertJsonFixtureEqualsJsonObject('serialize_deleted_resource.json', $resource);
    }

    public function testJsonSerializeEntry()
    {
        $space = $this->getMockBuilder(Space::class)
            ->disableOriginalConstructor()
            ->getMock();

        $space->method('getId')
            ->willReturn('cfexampleapi');

        $contentType = $this->getMockBuilder(ContentType::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contentType->method('getId')
            ->willReturn('human');

        $sys = new SystemProperties(
            '123',
            'Type',
            $space,
            $contentType,
            1,
            new DateTimeImmutable('2014-08-11T08:30:42.559Z'),
            new DateTimeImmutable('2014-08-12T08:30:42.559Z')
        );

        $this->assertJsonFixtureEqualsJsonObject('serialize_entry.json', $sys);
    }
}

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

        $sys = new SystemProperties([
            'id' => '123',
            'type' => 'Type',
            'space' => $space,
            'contentType' => $contentType,
            'revision' => 1,
            'createdAt' => new DateTimeImmutable('2014-08-11T08:30:42.559Z'),
            'updatedAt' => new DateTimeImmutable('2015-08-12T08:30:42.559Z'),
            'deletedAt' => new DateTimeImmutable('2016-08-13T08:30:42.559Z'),
        ]);

        $this->assertSame('123', $sys->getId());
        $this->assertSame('Type', $sys->getType());
        $this->assertSame($space, $sys->getSpace());
        $this->assertSame($contentType, $sys->getContentType());
        $this->assertSame($space, $sys->getSpace());
        $this->assertSame(1, $sys->getRevision());
        $this->assertSame('2014-08-11T08:30:42.559Z', (string) $sys->getCreatedAt());
        $this->assertSame('2015-08-12T08:30:42.559Z', (string) $sys->getUpdatedAt());
        $this->assertSame('2016-08-13T08:30:42.559Z', (string) $sys->getDeletedAt());
    }

    public function testJsonSerializeSpace()
    {
        $sys = new SystemProperties(['id' => '123', 'type' => 'Space']);

        $this->assertJsonFixtureEqualsJsonObject('serialize_space.json', $sys);
    }

    public function testJsonSerializeDeletedResource()
    {
        $space = $this->getMockBuilder(Space::class)
            ->disableOriginalConstructor()
            ->getMock();
        $space->method('getId')
            ->willReturn('cfexampleapi');

        $sys = new SystemProperties([
            'id' => '4rPdazIwWkuuKEAQgemSmO',
            'type' => 'DeletedEntry',
            'space' => $space,
            'revision' => 1,
            'createdAt' => new DateTimeImmutable('2014-08-11T08:30:42.559Z'),
            'updatedAt' => new DateTimeImmutable('2014-08-12T08:30:42.559Z'),
            'deletedAt' => new DateTimeImmutable('2014-08-13T08:30:42.559Z'),
        ]);

        $this->assertJsonFixtureEqualsJsonObject('serialize_deleted_resource.json', $sys);
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

        $sys = new SystemProperties([
            'id' => '123',
            'type' => 'Type',
            'space' => $space,
            'contentType' => $contentType,
            'revision' => 1,
            'createdAt' => new DateTimeImmutable('2014-08-11T08:30:42.559Z'),
            'updatedAt' => new DateTimeImmutable('2014-08-12T08:30:42.559Z'),
        ]);

        $this->assertJsonFixtureEqualsJsonObject('serialize_entry.json', $sys);
    }
}

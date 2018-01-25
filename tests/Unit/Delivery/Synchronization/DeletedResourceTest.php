<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\Delivery\Synchronization;

use Contentful\Delivery\ContentType;
use Contentful\Delivery\Space;
use Contentful\Delivery\Synchronization\DeletedEntry;
use Contentful\Delivery\Synchronization\DeletedResource;
use Contentful\Delivery\SystemProperties;

class DeletedResourceTest extends \PHPUnit_Framework_TestCase
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
            $createdAt = new \DateTimeImmutable('2014-08-11T08:30:42.559Z'),
            $updatedAt = new \DateTimeImmutable('2014-08-12T08:30:42.559Z'),
            $deletedAt = new \DateTimeImmutable('2014-08-13T08:30:42.559Z')
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
            new \DateTimeImmutable('2014-08-11T08:30:42.559Z'),
            new \DateTimeImmutable('2014-08-12T08:30:42.559Z'),
            new \DateTimeImmutable('2014-08-13T08:30:42.559Z')
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
            new \DateTimeImmutable('2014-08-11T08:30:42.559Z'),
            new \DateTimeImmutable('2014-08-12T08:30:42.559Z'),
            new \DateTimeImmutable('2014-08-13T08:30:42.559Z')
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
            new \DateTimeImmutable('2014-08-11T08:30:42.559Z'),
            new \DateTimeImmutable('2014-08-12T08:30:42.559Z'),
            new \DateTimeImmutable('2014-08-13T08:30:42.559Z')
        ));

        $this->assertJsonStringEqualsJsonString(
            '{"sys": {"type": "DeletedEntry","id": "4rPdazIwWkuuKEAQgemSmO","space": {"sys": {"type": "Link","linkType": "Space","id": "cfexampleapi"}},"revision": 1,"createdAt": "2014-08-11T08:30:42.559Z","updatedAt": "2014-08-12T08:30:42.559Z","deletedAt": "2014-08-13T08:30:42.559Z"}}',
            \json_encode($resource));
    }
}

class ConcreteDeletedResource extends DeletedResource
{
}

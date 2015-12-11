<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\Delivery\Synchronization;

use Contentful\Delivery\Synchronization\DeletedResource;
use Contentful\Delivery\Space;
use Contentful\Delivery\SystemProperties;

class DeletedResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Contentful\Delivery\Synchronization\DeletedResource::__construct
     * @covers Contentful\Delivery\Synchronization\DeletedResource::getId
     * @covers Contentful\Delivery\Synchronization\DeletedResource::getSpace
     * @covers Contentful\Delivery\Synchronization\DeletedResource::getRevision
     * @covers Contentful\Delivery\Synchronization\DeletedResource::getCreatedAt
     * @covers Contentful\Delivery\Synchronization\DeletedResource::getUpdatedAt
     * @covers Contentful\Delivery\Synchronization\DeletedResource::getDeletedAt
     *
     * @uses Contentful\Delivery\SystemProperties::__construct
     * @uses Contentful\Delivery\SystemProperties::getId
     * @uses Contentful\Delivery\SystemProperties::getSpace
     * @uses Contentful\Delivery\SystemProperties::getRevision
     * @uses Contentful\Delivery\SystemProperties::getCreatedAt
     * @uses Contentful\Delivery\SystemProperties::getUpdatedAt
     * @uses Contentful\Delivery\SystemProperties::getDeletedAt
     */
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
            new \DateTimeImmutable('2014-08-11T08:30:42.559Z'),
            new \DateTimeImmutable('2014-08-12T08:30:42.559Z'),
            new \DateTimeImmutable('2014-08-13T08:30:42.559Z')
        ));

        $this->assertEquals('4rPdazIwWkuuKEAQgemSmO', $resource->getId());
        $this->assertEquals($space, $resource->getSpace());
        $this->assertEquals(1, $resource->getRevision());
        $this->assertEquals(new \DateTimeImmutable('2014-08-11T08:30:42.559Z'), $resource->getCreatedAt());
        $this->assertEquals(new \DateTimeImmutable('2014-08-12T08:30:42.559Z'), $resource->getUpdatedAt());
        $this->assertEquals(new \DateTimeImmutable('2014-08-13T08:30:42.559Z'), $resource->getDeletedAt());
    }

    /**
     * @covers Contentful\Delivery\Synchronization\DeletedResource::__construct
     * @covers Contentful\Delivery\Synchronization\DeletedResource::jsonSerialize
     *
     * @uses Contentful\Delivery\SystemProperties::__construct
     * @uses Contentful\Delivery\SystemProperties::jsonSerialize
     * @uses Contentful\Delivery\SystemProperties::formatDateForJson
     */
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
            json_encode($resource));
    }
}

class ConcreteDeletedResource extends DeletedResource
{
}

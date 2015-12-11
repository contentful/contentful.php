<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\Delivery;

use Contentful\Delivery\SystemProperties;
use Contentful\Delivery\Space;
use Contentful\Delivery\ContentType;

class SystemPropertiesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Contentful\Delivery\SystemProperties::__construct
     * @covers Contentful\Delivery\SystemProperties::getId
     * @covers Contentful\Delivery\SystemProperties::getType
     * @covers Contentful\Delivery\SystemProperties::getSpace
     * @covers Contentful\Delivery\SystemProperties::getContentType
     * @covers Contentful\Delivery\SystemProperties::getRevision
     * @covers Contentful\Delivery\SystemProperties::getCreatedAt
     * @covers Contentful\Delivery\SystemProperties::getUpdatedAt
     * @covers Contentful\Delivery\SystemProperties::getDeletedAt
     */
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
            new \DateTimeImmutable('2014-08-11T08:30:42.559Z'),
            new \DateTimeImmutable('2014-08-12T08:30:42.559Z'),
            new \DateTimeImmutable('2014-08-13T08:30:42.559Z')
        );

        $this->assertEquals('123', $sys->getId());
        $this->assertEquals('Type', $sys->getType());
        $this->assertSame($space, $sys->getSpace());
        $this->assertSame($contentType, $sys->getContentType());
        $this->assertEquals(1, $sys->getRevision());
        $this->assertEquals(new \DateTimeImmutable('2014-08-11T08:30:42.559Z'), $sys->getCreatedAt());
        $this->assertEquals(new \DateTimeImmutable('2014-08-12T08:30:42.559Z'), $sys->getUpdatedAt());
        $this->assertEquals(new \DateTimeImmutable('2014-08-13T08:30:42.559Z'), $sys->getDeletedAt());
    }

    /**
     * @covers Contentful\Delivery\SystemProperties::__construct
     * @covers Contentful\Delivery\SystemProperties::jsonSerialize
     * @covers Contentful\Delivery\SystemProperties::formatDateForJson
     */
    public function testJsonSerializeSpace()
    {
        $sys = new SystemProperties('123', 'Space');

        $this->assertEquals(
            '{"id":"123","type":"Space"}',
            json_encode($sys)
        );
    }

    /**
     * @covers Contentful\Delivery\SystemProperties::__construct
     * @covers Contentful\Delivery\SystemProperties::jsonSerialize
     * @covers Contentful\Delivery\SystemProperties::formatDateForJson
     */
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
            new \DateTimeImmutable('2014-08-11T08:30:42.559Z'),
            new \DateTimeImmutable('2014-08-12T08:30:42.559Z'),
            new \DateTimeImmutable('2014-08-13T08:30:42.559Z')
        );

        $this->assertJsonStringEqualsJsonString(
            '{"type": "DeletedEntry","id": "4rPdazIwWkuuKEAQgemSmO","space": {"sys": {"type": "Link","linkType": "Space","id": "cfexampleapi"}},"revision": 1,"createdAt": "2014-08-11T08:30:42.559Z","updatedAt": "2014-08-12T08:30:42.559Z","deletedAt": "2014-08-13T08:30:42.559Z"}',
            json_encode($resource)
        );
    }

    /**
     * @covers Contentful\Delivery\SystemProperties::__construct
     * @covers Contentful\Delivery\SystemProperties::jsonSerialize
     * @covers Contentful\Delivery\SystemProperties::formatDateForJson
     */
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
            new \DateTimeImmutable('2014-08-11T08:30:42.559Z'),
            new \DateTimeImmutable('2014-08-12T08:30:42.559Z')
        );

        $this->assertJsonStringEqualsJsonString(
            '{"id":"123","type":"Type","space":{"sys":{"type":"Link","linkType":"Space","id":"cfexampleapi"}},"contentType":{"sys":{"type":"Link","linkType":"ContentType","id":"human"}},"revision":1,"createdAt":"2014-08-11T08:30:42.559Z","updatedAt":"2014-08-12T08:30:42.559Z"}',
            json_encode($sys)
        );
    }
}

<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\Delivery;

use Contentful\Delivery\ContentType;
use Contentful\Delivery\Space;
use Contentful\Delivery\ContentTypeField;
use Contentful\Delivery\SystemProperties;

class ContentTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Contentful\Delivery\ContentType::__construct
     * @covers Contentful\Delivery\ContentType::getId
     * @covers Contentful\Delivery\ContentType::getName
     * @covers Contentful\Delivery\ContentType::getDescription
     * @covers Contentful\Delivery\ContentType::getSpace
     * @covers Contentful\Delivery\ContentType::getDisplayField
     * @covers Contentful\Delivery\ContentType::getCreatedAt
     * @covers Contentful\Delivery\ContentType::getUpdatedAt
     * @covers Contentful\Delivery\ContentType::getRevision
     * @covers Contentful\Delivery\ContentType::getField
     * @covers Contentful\Delivery\ContentType::getFields
     *
     * @uses Contentful\Delivery\SystemProperties::__construct
     * @uses Contentful\Delivery\SystemProperties::getId
     * @uses Contentful\Delivery\SystemProperties::getSpace
     * @uses Contentful\Delivery\SystemProperties::getCreatedAt
     * @uses Contentful\Delivery\SystemProperties::getUpdatedAt
     * @uses Contentful\Delivery\SystemProperties::getRevision
     *
     * @uses Contentful\Delivery\ContentTypeField::__construct
     * @uses Contentful\Delivery\ContentTypeField::getId
     * @uses Contentful\Delivery\ContentTypeField::getName
     */
    public function testGetter()
    {
        $space = $this->getMockBuilder(Space::class)
            ->disableOriginalConstructor()
            ->getMock();

        $displayField = new ContentTypeField('name', 'Name', 'Text', null, null, null, true);

        $contentType = new ContentType(
            'Human',
            'Also called homo sapien',
            [
                $displayField,
                new ContentTypeField('likes', 'Likes', 'Array', null, 'Symbol', null),
                new ContentTypeField('image', 'Image', 'Array', null, 'Link', 'Asset', false, false, true)
            ],
            $displayField->getId(),
            new SystemProperties('human', 'ContentType', $space, null, 3, new \DateTimeImmutable('2013-06-27T22:46:14.133Z'), new \DateTimeImmutable('2013-09-02T15:10:26.818Z'))
        );

        $this->assertEquals('human', $contentType->getId());
        $this->assertEquals('Human', $contentType->getName());
        $this->assertEquals('Also called homo sapien', $contentType->getDescription());
        $this->assertEquals($space, $contentType->getSpace());
        $this->assertEquals($displayField, $contentType->getDisplayField());
        $this->assertEquals(new \DateTimeImmutable('2013-06-27T22:46:14.133Z'), $contentType->getCreatedAt());
        $this->assertEquals(new \DateTimeImmutable('2013-09-02T15:10:26.818Z'), $contentType->getUpdatedAt());
        $this->assertEquals(3, $contentType->getRevision());
        $this->assertEquals('Likes', $contentType->getField('likes')->getName());

        $fields = $contentType->getFields();
        $this->assertInternalType('array', $fields);
        $this->assertCount(3, $fields);
        $this->assertArrayHasKey('name', $fields);
        $this->assertEquals($displayField, $fields['name']);
    }

    /**
     * @covers Contentful\Delivery\ContentType::__construct
     * @covers Contentful\Delivery\ContentType::getDescription
     * @covers Contentful\Delivery\ContentType::getField
     * @covers Contentful\Delivery\ContentType::getDisplayField
     *
     * @uses Contentful\Delivery\SystemProperties::__construct
     * @uses Contentful\Delivery\SystemProperties::getId
     *
     * @uses Contentful\Delivery\ContentTypeField::__construct
     * @uses Contentful\Delivery\ContentTypeField::getId
     */
    public function testGetterNotExisting()
    {
        $space = $this->getMockBuilder(Space::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contentType = new ContentType(
            'Human',
            null,
            [
                new ContentTypeField('likes', 'Likes', 'Array', null, 'Symbol', null),
                new ContentTypeField('image', 'Image', 'Array', null, 'Link', 'Asset', false, false, true)
            ],
            null,
            new SystemProperties('human', 'ContentType', $space, null, 3, new \DateTimeImmutable('2013-06-27T22:46:14.133Z'), new \DateTimeImmutable('2013-09-02T15:10:26.818Z'))
        );

        $this->assertNull($contentType->getDescription());
        $this->assertNull($contentType->getField('notExisting'));
        $this->assertNull($contentType->getDisplayField());
    }

    /**
     * @covers Contentful\Delivery\ContentType::__construct
     * @covers Contentful\Delivery\ContentType::jsonSerialize
     *
     * @uses Contentful\Delivery\SystemProperties::__construct
     * @uses Contentful\Delivery\SystemProperties::getId
     * @uses Contentful\Delivery\SystemProperties::jsonSerialize
     * @uses Contentful\Delivery\SystemProperties::formatDateForJson
     *
     * @uses Contentful\Delivery\ContentTypeField::__construct
     * @uses Contentful\Delivery\ContentTypeField::getId
     * @uses Contentful\Delivery\ContentTypeField::jsonSerialize
     *
     *
     * For some reason phpunit claims this method is executed, no idea why.
     * @uses Contentful\Delivery\SystemProperties::getCreatedAt
     */
    public function testJsonSerialize()
    {
        $space = $this->getMockBuilder(Space::class)
            ->disableOriginalConstructor()
            ->getMock();

        $space->method('getId')
            ->willReturn('cfexampleapi');

        $displayField = new ContentTypeField('name', 'Name', 'Text', null, null, null, true);

        $contentType = new ContentType(
            'Human',
            'Also called homo sapien',
            [
                $displayField,
                new ContentTypeField('likes', 'Likes', 'Array', null, 'Symbol', null),
                new ContentTypeField('image', 'Image', 'Array', null, 'Link', 'Asset', false, false, true)
            ],
            $displayField->getId(),
            new SystemProperties('human', 'ContentType', $space, null, 3, new \DateTimeImmutable('2013-06-27T22:46:14.133Z'), new \DateTimeImmutable('2013-09-02T15:10:26.818Z'))
        );

        $this->assertJsonStringEqualsJsonString(
            '{"name":"Human","description":"Also called homo sapien","displayField":"name","sys":{"id":"human","type":"ContentType","space":{"sys":{"type":"Link","linkType":"Space","id":"cfexampleapi"}},"revision":3,"createdAt":"2013-06-27T22:46:14.133Z","updatedAt":"2013-09-02T15:10:26.818Z"},"fields":[{"name":"Name","id":"name","type":"Text","required":true,"localized":false},{"name":"Likes","id":"likes","type":"Array","required":false,"localized":false,"items":{"type":"Symbol"}},{"name":"Image","id":"image","type":"Array","required":false,"localized":false,"disabled":true,"items":{"type":"Link","linkType":"Asset"}}]}',
            json_encode($contentType)
        );
    }
}

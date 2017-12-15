<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\Delivery;

use Contentful\Delivery\ContentType;
use Contentful\Delivery\ContentTypeField;
use Contentful\Delivery\Space;
use Contentful\Delivery\SystemProperties;

class ContentTypeTest extends \PHPUnit_Framework_TestCase
{
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
                new ContentTypeField('image', 'Image', 'Array', null, 'Link', 'Asset', false, false, true),
            ],
            $displayField->getId(),
            new SystemProperties('human', 'ContentType', $space, null, 3, new \DateTimeImmutable('2013-06-27T22:46:14.133Z'), new \DateTimeImmutable('2013-09-02T15:10:26.818Z'))
        );

        $this->assertSame('human', $contentType->getId());
        $this->assertSame('Human', $contentType->getName());
        $this->assertSame('Also called homo sapien', $contentType->getDescription());
        $this->assertSame($space, $contentType->getSpace());
        $this->assertSame($displayField, $contentType->getDisplayField());
        $this->assertSame('2013-06-27T22:46:14.133Z', \Contentful\format_date_for_json($contentType->getCreatedAt()));
        $this->assertSame('2013-09-02T15:10:26.818Z', \Contentful\format_date_for_json($contentType->getUpdatedAt()));
        $this->assertSame(3, $contentType->getRevision());
        $this->assertSame('Likes', $contentType->getField('likes')->getName());

        $fields = $contentType->getFields();
        $this->assertInternalType('array', $fields);
        $this->assertCount(3, $fields);
        $this->assertArrayHasKey('name', $fields);
        $this->assertSame($displayField, $fields['name']);
    }

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
                new ContentTypeField('image', 'Image', 'Array', null, 'Link', 'Asset', false, false, true),
            ],
            null,
            new SystemProperties('human', 'ContentType', $space, null, 3, new \DateTimeImmutable('2013-06-27T22:46:14.133Z'), new \DateTimeImmutable('2013-09-02T15:10:26.818Z'))
        );

        $this->assertNull($contentType->getDescription());
        $this->assertNull($contentType->getField('notExisting'));
        $this->assertNull($contentType->getDisplayField());
    }

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
                new ContentTypeField('image', 'Image', 'Array', null, 'Link', 'Asset', false, false, true),
            ],
            $displayField->getId(),
            new SystemProperties('human', 'ContentType', $space, null, 3, new \DateTimeImmutable('2013-06-27T22:46:14.133Z'), new \DateTimeImmutable('2013-09-02T15:10:26.818Z'))
        );

        $this->assertJsonStringEqualsJsonString(
            '{"name":"Human","description":"Also called homo sapien","displayField":"name","sys":{"id":"human","type":"ContentType","space":{"sys":{"type":"Link","linkType":"Space","id":"cfexampleapi"}},"revision":3,"createdAt":"2013-06-27T22:46:14.133Z","updatedAt":"2013-09-02T15:10:26.818Z"},"fields":[{"name":"Name","id":"name","type":"Text","required":true,"localized":false},{"name":"Likes","id":"likes","type":"Array","required":false,"localized":false,"items":{"type":"Symbol"}},{"name":"Image","id":"image","type":"Array","required":false,"localized":false,"disabled":true,"items":{"type":"Link","linkType":"Asset"}}]}',
            \json_encode($contentType)
        );
    }
}

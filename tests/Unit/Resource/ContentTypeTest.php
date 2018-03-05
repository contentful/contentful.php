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
use Contentful\Delivery\Resource\ContentType\Field;
use Contentful\Delivery\Resource\Space;
use Contentful\Delivery\SystemProperties;
use Contentful\Tests\Delivery\TestCase;

class ContentTypeTest extends TestCase
{
    public function testGetter()
    {
        $space = $this->getMockBuilder(Space::class)
            ->disableOriginalConstructor()
            ->getMock();

        $displayField = new Field('name', 'Name', 'Text', null, null, null, true);

        $contentType = new ContentType(
            'Human',
            'Also called homo sapiens',
            [
                $displayField,
                new Field('likes', 'Likes', 'Array', null, 'Symbol', null),
                new Field('image', 'Image', 'Array', null, 'Link', 'Asset', false, false, true),
            ],
            $displayField->getId(),
            new SystemProperties('human', 'ContentType', $space, null, 3, new DateTimeImmutable('2013-06-27T22:46:14.133Z'), new DateTimeImmutable('2013-09-02T15:10:26.818Z'))
        );

        $this->assertSame('human', $contentType->getId());
        $this->assertSame('Human', $contentType->getName());
        $this->assertSame('Also called homo sapiens', $contentType->getDescription());
        $this->assertSame($space, $contentType->getSpace());
        $this->assertSame($displayField, $contentType->getDisplayField());
        $this->assertSame('2013-06-27T22:46:14.133Z', (string) $contentType->getCreatedAt());
        $this->assertSame('2013-09-02T15:10:26.818Z', (string) $contentType->getUpdatedAt());
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
                new Field('likes', 'Likes', 'Array', null, 'Symbol', null),
                new Field('image', 'Image', 'Array', null, 'Link', 'Asset', false, false, true),
            ],
            null,
            new SystemProperties('human', 'ContentType', $space, null, 3, new DateTimeImmutable('2013-06-27T22:46:14.133Z'), new DateTimeImmutable('2013-09-02T15:10:26.818Z'))
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

        $displayField = new Field('name', 'Name', 'Text', null, null, null, true);

        $contentType = new ContentType(
            'Human',
            'Also called homo sapiens',
            [
                $displayField,
                new Field('likes', 'Likes', 'Array', null, 'Symbol', null),
                new Field('image', 'Image', 'Array', null, 'Link', 'Asset', false, false, true),
            ],
            $displayField->getId(),
            new SystemProperties('human', 'ContentType', $space, null, 3, new DateTimeImmutable('2013-06-27T22:46:14.133Z'), new DateTimeImmutable('2013-09-02T15:10:26.818Z'))
        );

        $this->assertJsonFixtureEqualsJsonObject('serialize.json', $contentType);
    }
}

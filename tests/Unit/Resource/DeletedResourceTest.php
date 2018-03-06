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
use Contentful\Delivery\Resource\Space;
use Contentful\Delivery\SystemProperties;
use Contentful\Tests\Delivery\TestCase;

class DeletedResourceTest extends TestCase
{
    public function testGetter()
    {
        $sys = new SystemProperties([
            'id' => '4rPdazIwWkuuKEAQgemSmO',
            'type' => 'DeletedEntry',
            'revision' => 1,
            'createdAt' => new DateTimeImmutable('2014-08-11T08:30:42.559Z'),
            'updatedAt' => new DateTimeImmutable('2014-08-12T08:30:42.559Z'),
            'deletedAt' => new DateTimeImmutable('2014-08-13T08:30:42.559Z'),
        ]);
        $resource = new MockDeletedResource(['sys' => $sys]);

        $this->assertSame('4rPdazIwWkuuKEAQgemSmO', $resource->getId());
        $this->assertSame(1, $resource->getRevision());
        $this->assertSame('2014-08-11T08:30:42.559Z', (string) $resource->getCreatedAt());
        $this->assertSame('2014-08-12T08:30:42.559Z', (string) $resource->getUpdatedAt());
        $this->assertSame('2014-08-13T08:30:42.559Z', (string) $resource->getDeletedAt());
    }

    public function testContentTypeDeletedEntry()
    {
        $sys = new SystemProperties([
            'id' => '4rPdazIwWkuuKEAQgemSmO',
            'type' => 'DeletedEntry',
            'revision' => 1,
            'createdAt' => new DateTimeImmutable('2014-08-11T08:30:42.559Z'),
            'updatedAt' => new DateTimeImmutable('2014-08-12T08:30:42.559Z'),
            'deletedAt' => new DateTimeImmutable('2014-08-13T08:30:42.559Z'),
        ]);
        $deletedEntry = new MockDeletedEntry(['sys' => $sys]);

        $this->assertNull($deletedEntry->getContentType());

        $contentType = MockContentType::withSys('cat');
        $sys = new SystemProperties([
            'id' => '4rPdazIwWkuuKEAQgemSmO',
            'type' => 'DeletedEntry',
            'revision' => 1,
            'contentType' => $contentType,
            'createdAt' => new DateTimeImmutable('2014-08-11T08:30:42.559Z'),
            'updatedAt' => new DateTimeImmutable('2014-08-12T08:30:42.559Z'),
            'deletedAt' => new DateTimeImmutable('2014-08-13T08:30:42.559Z'),
        ]);
        $deletedEntry = new MockDeletedEntry(['sys' => $sys]);

        $this->assertSame($contentType, $deletedEntry->getContentType());
    }

    public function testJsonSerialize()
    {
        $sys = new SystemProperties([
            'id' => '4rPdazIwWkuuKEAQgemSmO',
            'type' => 'DeletedEntry',
            'space' => MockSpace::withSys('cfexampleapi'),
            'revision' => 1,
            'createdAt' => new DateTimeImmutable('2014-08-11T08:30:42.559Z'),
            'updatedAt' => new DateTimeImmutable('2014-08-12T08:30:42.559Z'),
            'deletedAt' => new DateTimeImmutable('2014-08-13T08:30:42.559Z'),
        ]);
        $resource = new MockDeletedResource(['sys' => $sys]);

        $this->assertJsonFixtureEqualsJsonObject('serialize.json', $resource);
    }
}

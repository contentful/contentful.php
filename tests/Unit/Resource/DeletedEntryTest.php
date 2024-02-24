<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Unit\Resource;

use Contentful\Delivery\SystemProperties\DeletedEntry as SystemProperties;
use Contentful\Tests\Delivery\Implementation\MockContentType;
use Contentful\Tests\Delivery\Implementation\MockDeletedEntry;
use Contentful\Tests\Delivery\Implementation\MockEnvironment;
use Contentful\Tests\Delivery\Implementation\MockSpace;
use Contentful\Tests\Delivery\TestCase;

class DeletedEntryTest extends TestCase
{
    public function testGetter()
    {
        $sys = new SystemProperties([
            'id' => '4rPdazIwWkuuKEAQgemSmO',
            'type' => 'DeletedEntry',
            'revision' => 1,
            'space' => MockSpace::withSys('spaceId'),
            'environment' => MockEnvironment::withSys('master'),
            'contentType' => MockContentType::withSys('contentTypeId'),
            'createdAt' => '2014-08-11T08:30:42.559Z',
            'updatedAt' => '2014-08-12T08:30:42.559Z',
            'deletedAt' => '2014-08-13T08:30:42.559Z',
        ]);
        $resource = new MockDeletedEntry(['sys' => $sys]);

        $this->assertSame('4rPdazIwWkuuKEAQgemSmO', $resource->getId());
        $sys = $resource->getSystemProperties();
        $this->assertSame(1, $sys->getRevision());
        $this->assertSame('2014-08-11T08:30:42.559Z', (string) $sys->getCreatedAt());
        $this->assertSame('2014-08-12T08:30:42.559Z', (string) $sys->getUpdatedAt());
        $this->assertSame('2014-08-13T08:30:42.559Z', (string) $sys->getDeletedAt());
    }

    public function testContentTypeDeletedEntry()
    {
        $contentType = MockContentType::withSys('cat');
        $sys = new SystemProperties([
            'id' => '4rPdazIwWkuuKEAQgemSmO',
            'type' => 'DeletedEntry',
            'revision' => 1,
            'space' => MockSpace::withSys('spaceId'),
            'environment' => MockEnvironment::withSys('master'),
            'contentType' => $contentType,
            'createdAt' => '2014-08-11T08:30:42.559Z',
            'updatedAt' => '2014-08-12T08:30:42.559Z',
            'deletedAt' => '2014-08-13T08:30:42.559Z',
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
            'environment' => MockEnvironment::withSys('master'),
            'contentType' => MockContentType::withSys('contentTypeId'),
            'revision' => 1,
            'createdAt' => '2014-08-11T08:30:42.559Z',
            'updatedAt' => '2014-08-12T08:30:42.559Z',
            'deletedAt' => '2014-08-13T08:30:42.559Z',
        ]);
        $resource = new MockDeletedEntry(['sys' => $sys]);

        $this->assertJsonFixtureEqualsJsonObject('serialize.json', $resource);
    }
}

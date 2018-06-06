<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery\Unit;

use Contentful\Core\Api\DateTimeImmutable;
use Contentful\Delivery\SystemProperties;
use Contentful\Tests\Delivery\TestCase;
use Contentful\Tests\Delivery\Unit\Resource\MockContentType;
use Contentful\Tests\Delivery\Unit\Resource\MockEnvironment;
use Contentful\Tests\Delivery\Unit\Resource\MockSpace;

class SystemPropertiesTest extends TestCase
{
    public function testGetter()
    {
        $space = MockSpace::withSys('cfexampleapi');
        $contentType = MockContentType::withSys('person');
        $environment = MockEnvironment::withSys('master');

        $sys = new SystemProperties([
            'id' => '123',
            'type' => 'Type',
            'space' => $space,
            'contentType' => $contentType,
            'environment' => $environment,
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
        $this->assertSame($environment, $sys->getEnvironment());
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
        $sys = new SystemProperties([
            'id' => '4rPdazIwWkuuKEAQgemSmO',
            'type' => 'DeletedEntry',
            'space' => MockSpace::withSys('cfexampleapi'),
            'revision' => 1,
            'createdAt' => new DateTimeImmutable('2014-08-11T08:30:42.559Z'),
            'updatedAt' => new DateTimeImmutable('2014-08-12T08:30:42.559Z'),
            'deletedAt' => new DateTimeImmutable('2014-08-13T08:30:42.559Z'),
        ]);

        $this->assertJsonFixtureEqualsJsonObject('serialize_deleted_resource.json', $sys);
    }

    public function testJsonSerializeEntry()
    {
        $sys = new SystemProperties([
            'id' => '123',
            'type' => 'Type',
            'space' => MockSpace::withSys('cfexampleapi'),
            'contentType' => MockContentType::withSys('human'),
            'environment' => MockEnvironment::withSys('master'),
            'revision' => 1,
            'createdAt' => new DateTimeImmutable('2014-08-11T08:30:42.559Z'),
            'updatedAt' => new DateTimeImmutable('2014-08-12T08:30:42.559Z'),
        ]);

        $this->assertJsonFixtureEqualsJsonObject('serialize_entry.json', $sys);
    }
}

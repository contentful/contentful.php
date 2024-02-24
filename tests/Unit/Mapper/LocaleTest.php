<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Unit\Mapper;

use Contentful\Delivery\Mapper\Locale as Mapper;
use Contentful\Delivery\Resource\Locale;
use Contentful\Tests\Delivery\Implementation\MockClient;
use Contentful\Tests\Delivery\Implementation\MockParser;
use Contentful\Tests\Delivery\Implementation\MockResourceBuilder;
use Contentful\Tests\Delivery\TestCase;

class LocaleTest extends TestCase
{
    public function testMapper()
    {
        $mapper = new Mapper(
            new MockResourceBuilder(),
            new MockClient(),
            new MockParser()
        );

        /** @var Locale $resource */
        $resource = $mapper->map(null, [
            'sys' => [
                'id' => 'en-US',
                'type' => 'Locale',
                'version' => 1,
            ],
            'code' => 'en-US',
            'name' => 'English (United States)',
            'default' => true,
            'fallbackCode' => null,
        ]);

        $this->assertInstanceOf(Locale::class, $resource);
        $this->assertSame('en-US', $resource->getId());
        $this->assertSame('Locale', $resource->getType());

        $this->assertSame(1, $resource->getSystemProperties()->getRevision());

        $this->assertSame('en-US', $resource->getCode());
        $this->assertSame('English (United States)', $resource->getName());
        $this->assertTrue($resource->isDefault());
        $this->assertNull($resource->getFallbackCode());
    }
}

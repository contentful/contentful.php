<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Unit\Mapper;

use Contentful\Delivery\Mapper\Environment as Mapper;
use Contentful\Delivery\Resource\Environment;
use Contentful\Delivery\Resource\Locale;
use Contentful\Tests\Delivery\Implementation\MockClient;
use Contentful\Tests\Delivery\Implementation\MockParser;
use Contentful\Tests\Delivery\Implementation\MockResourceBuilder;
use Contentful\Tests\Delivery\TestCase;

class EnvironmentTest extends TestCase
{
    public function testMapper()
    {
        $mapper = new Mapper(
            new MockResourceBuilder(),
            new MockClient(),
            new MockParser()
        );

        /** @var Environment $resource */
        $resource = $mapper->map(null, [
            'sys' => [
                'id' => 'master',
                'type' => 'Environment',
            ],
            'locales' => [
                [
                    'sys' => [
                        'type' => 'Locale',
                        'id' => 'en-US',
                        'version' => 1,
                    ],
                    'code' => 'en-US',
                    'name' => 'English (United States)',
                    'default' => true,
                    'fallbackCode' => null,
                ],
            ],
        ]);

        $this->assertInstanceOf(Environment::class, $resource);
        $this->assertSame('master', $resource->getId());
        $this->assertSame('Environment', $resource->getType());

        $this->assertContainsOnlyInstancesOf(Locale::class, $resource->getLocales());
    }
}

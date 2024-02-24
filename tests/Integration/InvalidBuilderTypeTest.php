<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Integration;

use Contentful\Delivery\ResourceBuilder;
use Contentful\RichText\Parser;
use Contentful\Tests\Delivery\Implementation\LinkResolver;
use Contentful\Tests\Delivery\Implementation\MockClient;
use Contentful\Tests\Delivery\Implementation\MockResourcePool;
use Contentful\Tests\Delivery\TestCase;

class InvalidBuilderTypeTest extends TestCase
{
    public function testExceptionOnInvalidSysType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unexpected system type "invalidType" while trying to build a resource.');

        $builder = new ResourceBuilder(
            new MockClient(),
            new MockResourcePool(),
            new Parser(new LinkResolver())
        );

        $builder->build([
            'sys' => [
                'type' => 'invalidType',
                'id' => 'invalidId',
            ],
        ]);
    }
}

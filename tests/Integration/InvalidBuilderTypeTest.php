<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
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
    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Unexpected system type "invalidType" while trying to build a resource.
     */
    public function testExceptionOnInvalidSysType()
    {
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

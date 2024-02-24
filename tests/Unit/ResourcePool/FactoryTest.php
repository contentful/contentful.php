<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Unit\ResourcePool;

use Contentful\Delivery\ClientOptions;
use Contentful\Delivery\ResourcePool\Extended;
use Contentful\Delivery\ResourcePool\Factory;
use Contentful\Delivery\ResourcePool\Standard;
use Contentful\Tests\Delivery\Implementation\JsonDecoderClient;
use Contentful\Tests\Delivery\TestCase;

class FactoryTest extends TestCase
{
    public function testCorrectObjectIsCreates()
    {
        $client = new JsonDecoderClient();

        $options = (new ClientOptions())
            ->withLowMemoryResourcePool()
        ;
        $this->assertInstanceOf(Standard::class, Factory::create($client, $options));

        $this->assertInstanceOf(Extended::class, Factory::create($client, new ClientOptions()));
    }
}

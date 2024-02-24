<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Unit\Resource;

use Contentful\Tests\Delivery\Implementation\MockSpace;
use Contentful\Tests\Delivery\TestCase;

class SpaceTest extends TestCase
{
    public function testGetter()
    {
        $space = MockSpace::withSys('cfexampleapi', ['name' => 'Space name']);

        $this->assertSame('cfexampleapi', $space->getId());
        $this->assertSame('Space name', $space->getName());
    }

    public function testJsonSerialize()
    {
        $space = MockSpace::withSys('cfexampleapi', ['name' => 'Space name']);

        $this->assertJsonFixtureEqualsJsonObject('serialize.json', $space);
    }
}

<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Unit\SystemProperties;

use Contentful\Delivery\SystemProperties\Environment;
use Contentful\Tests\Delivery\TestCase;

class EnvironmentTest extends TestCase
{
    public function testSys()
    {
        $sys = new Environment([
            'id' => 'environmentId',
            'type' => 'Environment',
        ]);

        $this->assertSame('environmentId', $sys->getId());
        $this->assertSame('Environment', $sys->getType());

        $this->assertJsonFixtureEqualsJsonObject('serialize.json', $sys);
    }
}

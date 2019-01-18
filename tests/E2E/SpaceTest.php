<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2019 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\E2E;

use Contentful\Delivery\Resource\Space;
use Contentful\Tests\Delivery\TestCase;

class SpaceTest extends TestCase
{
    /**
     * @vcr space_get.json
     */
    public function testGet()
    {
        $client = $this->getClient('default');

        $space = $client->getSpace();

        $this->assertInstanceOf(Space::class, $space);
        $this->assertSame('Contentful Example API', $space->getName());
        $this->assertSame('cfexampleapi', $space->getId());
    }
}

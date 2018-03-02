<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery\E2E;

use Contentful\Delivery\Resource\Space;
use Contentful\Tests\Delivery\TestCase;

class SpaceTest extends TestCase
{
    /**
     * @vcr e2e_space_get.json
     */
    public function testGetSpace()
    {
        $client = $this->getClient('cfexampleapi');

        $space = $client->getSpace();

        $this->assertInstanceOf(Space::class, $space);
        $this->assertSame('Contentful Example API', $space->getName());
        $this->assertSame('cfexampleapi', $space->getId());
        $this->assertCount(2, $space->getLocales());
    }
}

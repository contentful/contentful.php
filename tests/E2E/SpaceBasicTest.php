<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Space;
use Contentful\Tests\Delivery\End2EndTestCase;

class SpaceBasicTest extends End2EndTestCase
{
    /**
     * @vcr e2e_space_get.json
     */
    public function testGetSpace()
    {
        $client = $this->getClient('cfexampleapi');

        $space = $client->getSpace();

        $this->assertInstanceOf(Space::class, $space);
        $this->assertEquals('Contentful Example API', $space->getName());
        $this->assertEquals('cfexampleapi', $space->getId());
        $this->assertCount(2, $space->getLocales());
    }
}

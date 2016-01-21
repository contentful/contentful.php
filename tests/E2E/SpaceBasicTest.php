<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Client;
use Contentful\Delivery\Space;

class SpaceBasicTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client
     */
    private $client;

    public function setUp()
    {
        $this->client = new Client('b4c0n73n7fu1', 'cfexampleapi');
    }

    /**
     * @vcr e2e_space_get.json
     */
    public function testGetSpace()
    {
        $space = $this->client->getSpace();

        $this->assertInstanceOf(Space::class, $space);
        $this->assertEquals('Contentful Example API', $space->getName());
        $this->assertEquals('cfexampleapi', $space->getId());
        $this->assertCount(2, $space->getLocales());
    }
}

<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Client;
use Contentful\ResourceArray;
use Contentful\Delivery\DynamicEntry;

class EntryBasicTest extends \PHPUnit_Framework_TestCase
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
     * @vcr e2e_entry_get_all.json
     */
    public function testGetAll()
    {
        $assets = $this->client->getEntries();

        $this->assertInstanceOf(ResourceArray::class, $assets);
    }

    /**
     * @vcr e2e_entry_get_one.json
     */
    public function testGetOne()
    {
        $entry = $this->client->getEntry('nyancat');

        $this->assertInstanceOf(DynamicEntry::class, $entry);
        $this->assertEquals('nyancat', $entry->getId());
    }
}

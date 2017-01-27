<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Client;
use Contentful\Delivery\Query;
use Contentful\ResourceArray;
use Contentful\Delivery\DynamicEntry;
use Contentful\Delivery\Asset;

class EntrySelectTest extends \PHPUnit_Framework_TestCase
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
     * @vcr e2e_entry_select_metadata.json
     */
    public function testSelectOnlyMetatdata()
    {
        $query = (new Query)
            ->setContentType('cat')
            ->select(['sys'])
            ->where('sys.id', 'nyancat')
            ->setLimit(1);
        $entries = $this->client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertNull($entries[0]->getName());
        $this->assertNull($entries[0]->getBestFriend());
    }

    /**
     * @vcr e2e_entry_select_one_field.json
     */
    public function testSelectOnlyOneField()
    {
        $query = (new Query)
            ->setContentType('cat')
            ->select(['fields.name'])
            ->where('sys.id', 'nyancat')
            ->setLimit(1);
        $entries = $this->client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertEquals('Nyan Cat', $entries[0]->getName());
        $this->assertNull($entries[0]->getBestFriend());
    }
}

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

class EntryLocaleTest extends \PHPUnit_Framework_TestCase
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
     * @vcr e2e_entry_locale_get_all.json
     */
    public function testGetAll()
    {
        $query = (new Query)
            ->setContentType('cat')
            ->setLocale('*');
        $entries = $this->client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertEquals('Nyan Cat', $entries[0]->getName());
    }

    /**
     * @vcr e2e_entry_locale_get_en_us.json
     */
    public function testGetEnUs()
    {
        $query = (new Query)
            ->setContentType('cat')
            ->setLocale('en-US');
        $entries = $this->client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertEquals('Nyan Cat', $entries[0]->getName());
    }

    /**
     * @vcr e2e_entry_locale_get_tlh.json
     */
    public function testGetTlh()
    {
        $query = (new Query)
            ->setContentType('cat')
            ->setLocale('tlh');
        $entries = $this->client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertEquals('Nyan vIghro\'', $entries[0]->getName());
    }

    /**
     * @vcr e2e_entry_locale_from_client.json
     */
    public function testGetLocaleFromClient()
    {
        $client = new Client('b4c0n73n7fu1', 'cfexampleapi', false, 'tlh');

        $query = (new Query)
            ->setContentType('cat');
        $entries = $client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertEquals('Nyan vIghro\'', $entries[0]->getName());
        $this->assertNull($entries[0]->getName('en-US'));
    }
}

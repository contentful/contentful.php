<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Query;
use Contentful\Delivery\Client;
use Contentful\ResourceArray;

class EntrySearchTest extends \PHPUnit_Framework_TestCase
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
     * @vcr e2e_entry_search_by_content_type.json
     */
    public function testSearchByContentType()
    {
        $query = (new Query())
            ->setContentType('cat');

        $entries = $this->client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
    }

    /**
     * @vcr e2e_entry_search_equality.json
     */
    public function testSearchEquality()
    {
        $query = (new Query())
            ->where('sys.id', 'nyancat');

        $entries = $this->client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertCount(1, $entries);
    }

    /**
     * @vcr e2e_entry_search_inequality.json
     */
    public function testSearchInequality()
    {
        $query = (new Query())
            ->where('sys.id', 'nyancat', 'ne');

        $entries = $this->client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertCount(9, $entries);
    }

    /**
     * @vcr e2e_entry_search_array_equals.json
     */
    public function testSearchArrayEquality()
    {
        $query = (new Query())
            ->setContentType('cat')
            ->where('fields.likes', 'lasagna');

        $entries = $this->client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
    }

    /**
     * @vcr e2e_entry_search_inclusion.json
     */
    public function testSearchInclusion()
    {
        $query = (new Query())
            ->where('sys.id', 'finn,jake', 'in');

        $entries = $this->client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertCount(2, $entries);
    }

    // Existance test has problems with php-vcr

    /**
     * @vcr e2e_entry_search_range.json
     */
    public function testSearchRange()
    {
        $query = (new Query())
            ->where('sys.updatedAt', new \DateTimeImmutable('2013-01-01T00:00:00Z'), 'lte');

        $entries = $this->client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
    }

    /**
     * @vcr e2e_entry_search_full_text.json
     */
    public function testSearchFullText()
    {
        $query = (new Query())
            ->where('query', 'bacon');

        $entries = $this->client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
    }

    /**
     * @vcr e2e_entry_search_full_text_on_field.json
     */
    public function testSearchFullTextOnField()
    {
        $query = (new Query())
            ->setContentType('dog')
            ->where('fields.description', 'bacon pancakes', 'match');

        $entries = $this->client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
    }
}

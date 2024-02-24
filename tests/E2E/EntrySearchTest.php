<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\E2E;

use Contentful\Core\Api\DateTimeImmutable;
use Contentful\Core\Resource\ResourceArray;
use Contentful\Delivery\Query;
use Contentful\Delivery\Resource\Entry;
use Contentful\Tests\Delivery\TestCase;

class EntrySearchTest extends TestCase
{
    /**
     * @vcr entry_search_by_content_type.json
     */
    public function testByContentType()
    {
        $client = $this->getClient('default');

        $query = (new Query())
            ->setContentType('cat')
        ;

        $entries = $client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
    }

    /**
     * @vcr entry_search_equality.json
     */
    public function testEquality()
    {
        $client = $this->getClient('default');

        $query = (new Query())
            ->where('sys.id', 'nyancat')
        ;

        $entries = $client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertCount(1, $entries);
    }

    /**
     * @vcr entry_search_inequality.json
     */
    public function testInequality()
    {
        $client = $this->getClient('default');

        $query = (new Query())
            ->where('sys.id[ne]', 'nyancat')
        ;

        $entries = $client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertCount(9, $entries);
    }

    /**
     * @vcr entry_search_array_equality.json
     */
    public function testArrayEquality()
    {
        $client = $this->getClient('default');

        $query = (new Query())
            ->setContentType('cat')
            ->where('fields.likes', 'lasagna')
        ;

        $entries = $client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
    }

    /**
     * @vcr entry_search_inclusion.json
     */
    public function testInclusion()
    {
        $client = $this->getClient('default');

        $query = (new Query())
            ->where('sys.id[in]', 'finn,jake')
        ;

        $entries = $client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertCount(2, $entries);
    }

    /**
     * @vcr entry_search_range.json
     */
    public function testRange()
    {
        $client = $this->getClient('default');

        $query = (new Query())
            ->where('sys.updatedAt[lte]', new DateTimeImmutable('2013-01-01T00:00:00Z'))
        ;

        $entries = $client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
    }

    /**
     * @vcr entry_search_full_text.json
     */
    public function testFullText()
    {
        $client = $this->getClient('default');

        $query = (new Query())
            ->where('query', 'bacon')
        ;

        $entries = $client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
    }

    /**
     * @vcr entry_search_full_text_on_field.json
     */
    public function testFullTextOnField()
    {
        $client = $this->getClient('default');

        $query = (new Query())
            ->setContentType('dog')
            ->where('fields.description[match]', 'bacon pancakes')
        ;

        $entries = $client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
    }

    /**
     * @vcr entry_search_links_to_entries.json
     */
    public function testLinksToEntries()
    {
        $client = $this->getClient('default');

        $query = (new Query())
            ->linksToEntry('nyancat')
        ;

        $entries = $client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertCount(1, $entries);
        $this->assertInstanceOf(Entry::class, $entries[0]);
        $this->assertSame('happycat', $entries[0]->getId());
    }

    /**
     * @vcr entry_search_links_to_assets.json
     */
    public function testLinksToAssets()
    {
        $client = $this->getClient('default');

        $query = (new Query())
            ->linksToAsset('nyancat')
        ;

        $entries = $client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertCount(1, $entries);
        $this->assertInstanceOf(Entry::class, $entries[0]);
        $this->assertSame('nyancat', $entries[0]->getId());
    }
}

<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery\E2E;

use Contentful\Core\Resource\ResourceArray;
use Contentful\Delivery\Query;
use Contentful\Delivery\Resource\Asset;
use Contentful\Delivery\Resource\Entry;
use Contentful\Delivery\Resource\Environment;
use Contentful\Delivery\Resource\Space;
use Contentful\Tests\Delivery\TestCase;

/**
 * Test that objects can be constructed successfullly in various scenarios.
 */
class EntryTest extends TestCase
{
    /**
     * @vcr e2e_entry_get_all_locale_all.json
     */
    public function testGetAll()
    {
        $client = $this->getClient('cfexampleapi');

        $query = (new Query())
            ->setLocale('*');
        $assets = $client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $assets);
    }

    /**
     * @vcr e2e_entry_get_all_locale_default.json
     */
    public function testGetAllDefaultLocale()
    {
        $client = $this->getClient('cfexampleapi');

        $assets = $client->getEntries();

        $this->assertInstanceOf(ResourceArray::class, $assets);
    }

    /**
     * @vcr e2e_entry_get_one_locale_all.json
     */
    public function testGetOne()
    {
        $client = $this->getClient('cfexampleapi');

        $entry = $client->getEntry('nyancat', '*');

        $this->assertInstanceOf(Entry::class, $entry);
        $this->assertSame('nyancat', $entry->getId());
        $this->assertSame('nyancat', $entry->getSystemProperties()->getId());
        $this->assertSame('Entry', $entry->getSystemProperties()->getType());
        $this->assertLink('nyancat', 'Entry', $entry->asLink());
        $this->assertInstanceOf(Environment::class, $entry->getEnvironment());
        $this->assertInstanceOf(Space::class, $entry->getSpace());
    }

    /**
     * @vcr e2e_entry_get_one_locale_default.json
     */
    public function testGetOneDefaultLocale()
    {
        $client = $this->getClient('cfexampleapi');

        $entry = $client->getEntry('nyancat');

        $this->assertInstanceOf(Entry::class, $entry);
        $this->assertSame('nyancat', $entry->getId());
    }

    /**
     * @vcr e2e_entry_lazy_loading.json
     */
    public function testLazyLoading()
    {
        $client = $this->getClient('cfexampleapi');

        $entry = $client->getEntry('nyancat');
        $bestFriend = $entry->getBestFriend();

        $this->assertInstanceOf(Entry::class, $entry);
        $this->assertSame('happycat', $bestFriend->getId());

        $image = $entry->getImage();

        $this->assertInstanceOf(Asset::class, $image);
        $this->assertSame('nyancat', $image->getId());
    }

    /**
     * @vcr e2e_entry_lazy_loading_cached.json
     */
    public function testLazyLoadIsCached()
    {
        $client = $this->getClient('cfexampleapi');

        $nyancat = $client->getEntry('nyancat');
        $bestFriend = $nyancat->getBestFriend();

        // Locally it's cached
        $this->assertSame('happycat', $bestFriend->getId());
        $this->assertSame($bestFriend, $nyancat->getBestFriend());

        // but not globally
        $happycat = $client->getEntry('happycat');
        $this->assertSame($bestFriend->getId(), $happycat->getId());
        $this->assertNotSame($bestFriend, $happycat);
    }

    /**
     * @vcr e2e_entry_withing_graph_identical.json
     */
    public function testEntriesWithinGraphAreIdentical()
    {
        $client = $this->getClient('cfexampleapi');

        $query = (new Query())
            ->where('sys.id', 'nyancat');
        $nyancat = $client->getEntries($query)[0];
        $bestFriend = $nyancat->getBestFriend();
        $bestFriendsBestFriend = $bestFriend->getBestFriend();

        $this->assertSame('nyancat', $bestFriendsBestFriend->getId());
        $this->assertSame($nyancat, $bestFriendsBestFriend);
    }

    /**
     * @vcr e2e_entry_assets_resolved_from_includes.json
     */
    public function testAssetsResolvedFromIncludes()
    {
        $client = $this->getClient('cfexampleapi');

        $query = (new Query())
            ->where('sys.id', 'nyancat');
        $nyancat = $client->getEntries($query)[0];
        $image = $nyancat->getImage();

        $this->assertSame('nyancat', $image->getId());

        // There should be 4 and only 4 requests:
        // the entries with the query, the space, the locales and the cat content type.
        $this->assertCount(4, $client->getMessages());
    }

    /**
     * @vcr e2e_entry_resolved_links_to_itself.json
     */
    public function testEntryResolvedLinksToItself()
    {
        $client = $this->getClient('cfexampleapi');
        $entry = $client->getEntry('nyancat');

        $references = $entry->getReferences();
        $this->assertInstanceOf(ResourceArray::class, $references);
        $this->assertCount(1, $references);
        $this->assertInstanceOf(Entry::class, $references[0]);
        $this->assertSame('happycat', $references[0]->getId());
    }

    /**
     * @vcr e2e_entry_select_metadata.json
     */
    public function testSelectOnlyMetadata()
    {
        $client = $this->getClient('cfexampleapi');

        $query = (new Query())
            ->setContentType('cat')
            ->select(['sys'])
            ->where('sys.id', 'nyancat')
            ->setLimit(1);
        $entries = $client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertNull($entries[0]->getName());
        $this->assertNull($entries[0]->getBestFriend());
        $this->assertNull($entries[0]->get('name'));
        $this->assertNull($entries[0]->get('bestFriend'));
        $this->assertNull($entries[0]->name);
        $this->assertNull($entries[0]->bestFriend);
        $this->assertNull($entries[0]['name']);
        $this->assertNull($entries[0]['bestFriend']);
    }

    /**
     * @vcr e2e_entry_select_one_field.json
     */
    public function testSelectOnlyOneField()
    {
        $client = $this->getClient('cfexampleapi');

        $query = (new Query())
            ->setContentType('cat')
            ->select(['fields.name'])
            ->where('sys.id', 'nyancat')
            ->setLimit(1);
        $entries = $client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertSame('Nyan Cat', $entries[0]->getName());
        $this->assertNull($entries[0]->getBestFriend());
        $this->assertSame('Nyan Cat', $entries[0]->get('name'));
        $this->assertNull($entries[0]->get('bestFriend'));
        $this->assertSame('Nyan Cat', $entries[0]->name);
        $this->assertNull($entries[0]->bestFriend);
        $this->assertSame('Nyan Cat', $entries[0]['name']);
        $this->assertNull($entries[0]['bestFriend']);
    }
}

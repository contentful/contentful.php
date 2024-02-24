<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\E2E;

use Contentful\Core\Resource\ResourceArray;
use Contentful\Delivery\Query;
use Contentful\Delivery\Resource\Asset;
use Contentful\Delivery\Resource\Entry;
use Contentful\Delivery\Resource\Environment;
use Contentful\Delivery\Resource\Space;
use Contentful\Tests\Delivery\TestCase;

/**
 * Test that objects can be constructed successfully in various scenarios.
 */
class EntryTest extends TestCase
{
    /**
     * @vcr entry_get_all.json
     */
    public function testGetAll()
    {
        $client = $this->getClient('default');

        $query = (new Query())
            ->setLocale('*')
        ;
        $assets = $client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $assets);
    }

    /**
     * @vcr entry_get_all_default_locale.json
     */
    public function testGetAllDefaultLocale()
    {
        $client = $this->getClient('default');

        $assets = $client->getEntries();

        $this->assertInstanceOf(ResourceArray::class, $assets);
    }

    /**
     * @vcr entry_get_one.json
     */
    public function testGetOne()
    {
        $client = $this->getClient('default');

        $entry = $client->getEntry('nyancat', '*');

        $this->assertInstanceOf(Entry::class, $entry);
        $this->assertSame('nyancat', $entry->getId());
        $this->assertSame('nyancat', $entry->getSystemProperties()->getId());
        $this->assertSame('Entry', $entry->getSystemProperties()->getType());
        $this->assertLink('nyancat', 'Entry', $entry->asLink());
        $this->assertInstanceOf(Environment::class, $entry->getEnvironment());
        $this->assertInstanceOf(Space::class, $entry->getSpace());

        $fields = $entry->all();
        $this->assertSame('Nyan Cat', $fields['name']);
        $this->assertSame(['rainbows', 'fish'], $fields['likes']);
        $this->assertSame('rainbow', $fields['color']);
        $this->assertInstanceOf(Entry::class, $fields['bestFriend']);
        $this->assertSame('happycat', $fields['bestFriend']->getId());
        $this->assertSame('2011-04-04T22:00:00Z', (string) $fields['birthday']);
        $this->assertNull($fields['lifes']);
        $this->assertSame(1337, $fields['lives']);
        $this->assertInstanceOf(Asset::class, $fields['image']);
        $this->assertSame('nyancat', $fields['image']->getId());
    }

    /**
     * @vcr entry_get_one_default_locale.json
     */
    public function testGetOneDefaultLocale()
    {
        $client = $this->getClient('default');

        $entry = $client->getEntry('nyancat');

        $this->assertInstanceOf(Entry::class, $entry);
        $this->assertSame('nyancat', $entry->getId());
    }

    /**
     * @vcr entry_lazy_loading.json
     */
    public function testLazyLoading()
    {
        $client = $this->getClient('default');

        $entry = $client->getEntry('nyancat');
        $bestFriend = $entry->getBestFriend();

        $this->assertInstanceOf(Entry::class, $entry);
        $this->assertSame('happycat', $bestFriend->getId());

        $image = $entry->getImage();

        $this->assertInstanceOf(Asset::class, $image);
        $this->assertSame('nyancat', $image->getId());
    }

    /**
     * @vcr entry_lazy_load_is_cached.json
     */
    public function testLazyLoadIsCached()
    {
        $client = $this->getClient('default');

        $nyancat = $client->getEntry('nyancat');
        $bestFriend = $nyancat->getBestFriend();

        // Locally it's cached
        $this->assertSame('happycat', $bestFriend->getId());
        $this->assertSame($bestFriend, $nyancat->getBestFriend());

        // and also globally
        $happycat = $client->getEntry('happycat');
        $this->assertSame($bestFriend->getId(), $happycat->getId());
        $this->assertSame($bestFriend, $happycat);
    }

    /**
     * @vcr entry_entries_within_graph_are_identical.json
     */
    public function testEntriesWithinGraphAreIdentical()
    {
        $client = $this->getClient('default');

        $query = (new Query())
            ->where('sys.id', 'nyancat')
        ;
        $nyancat = $client->getEntries($query)[0];
        $bestFriend = $nyancat->getBestFriend();
        $bestFriendsBestFriend = $bestFriend->getBestFriend();

        $this->assertSame('nyancat', $bestFriendsBestFriend->getId());
        $this->assertSame($nyancat, $bestFriendsBestFriend);
    }

    /**
     * @vcr entry_assets_resolved_from_includes.json
     */
    public function testAssetsResolvedFromIncludes()
    {
        $client = $this->getClient('default');

        $query = (new Query())
            ->where('sys.id', 'nyancat')
        ;
        $nyancat = $client->getEntries($query)[0];
        $image = $nyancat->getImage();

        $this->assertSame('nyancat', $image->getId());

        // There should be 4 and only 4 requests:
        // the entries with the query, the space, the locales and the cat content type.
        $this->assertCount(4, $client->getMessages());
    }

    /**
     * @vcr entry_assets_resolved_from_includes_with_all_locales.json
     */
    public function testAssetsResolvedFromIncludesWithAllLocales()
    {
        $client = $this->getClient('default');

        $query = (new Query())
            ->where('sys.id', 'nyancat')
            ->setLocale('*')
        ;

        $nyancat = $client->getEntries($query)[0];
        $image = $nyancat->get('image');

        $this->assertSame('nyancat', $image->getId());

        // There should be 4 and only 4 requests:
        // the entries with the query, the space, the locales and the cat content type.
        $this->assertCount(4, $client->getMessages());
    }

    /**
     * @vcr entry_resolved_links_to_itself.json
     */
    public function testEntryResolvedLinksToItself()
    {
        $client = $this->getClient('default');
        $entry = $client->getEntry('nyancat');

        $references = $entry->getReferences();
        $this->assertInstanceOf(ResourceArray::class, $references);
        $this->assertCount(1, $references);
        $this->assertInstanceOf(Entry::class, $references[0]);
        $this->assertSame('happycat', $references[0]->getId());
    }

    /**
     * @vcr entry_select_only_metadata.json
     */
    public function testSelectOnlyMetadata()
    {
        $client = $this->getClient('default');

        $query = (new Query())
            ->setContentType('cat')
            ->select(['sys'])
            ->where('sys.id', 'nyancat')
            ->setLimit(1)
        ;
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
     * @vcr entry_select_only_one_field.json
     */
    public function testSelectOnlyOneField()
    {
        $client = $this->getClient('default');

        $query = (new Query())
            ->setContentType('cat')
            ->select(['fields.name'])
            ->where('sys.id', 'nyancat')
            ->setLimit(1)
        ;
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

    /**
     * @vcr entry_partial_building_with_default_locale.json
     */
    public function testPartialBuildingWithDefaultLocale()
    {
        $client = $this->getClient('new');

        $query = (new Query())
            ->setContentType('complexContentType')
            ->select(['fields.link', 'fields.location', 'fields.date', 'fields.arrayOfLinks'])
            ->where('sys.id', '5teS5mSVJ66qg6QOIY0SWI')
        ;
        /** @var Entry $entry */
        $entry = $client->getEntries($query)[0];

        $this->assertTrue($entry->has('date'));
        $this->assertSame('2017-12-31T22:00:00Z', (string) $entry->get('date'));

        $this->assertTrue($entry->has('location'));
        $location = $entry->get('location');
        $this->assertEqualsWithDelta(43.7682899, $location->getLatitude(), 0.0001);
        $this->assertEqualsWithDelta(11.2556199, $location->getLongitude(), 0.0001);

        $this->assertTrue($entry->has('link'));
        $link = $entry->get('link', null, false);
        $this->assertLink('SQOIQ1rZMQQUeyoyGiEUq', 'Asset', $link);

        $this->assertTrue($entry->has('arrayOfLinks'));
        $arrayOfLinks = $entry->get('arrayOfLinks', null, false);
        $this->assertLink('5teS5mSVJ66qg6QOIY0SWI', 'Entry', $arrayOfLinks[0]);

        $this->assertFalse($entry->has('text'));
        $this->assertFalse($entry->has('boolean'));

        $query = (new Query())
            ->setContentType('complexContentType')
            ->select(['fields.text', 'fields.boolean'])
            ->where('sys.id', '5teS5mSVJ66qg6QOIY0SWI')
        ;
        $entry = $client->getEntries($query)[0];

        $this->assertTrue($entry->has('date'));
        $this->assertSame('2017-12-31T22:00:00Z', (string) $entry->get('date'));

        $this->assertTrue($entry->has('location'));
        $location = $entry->get('location');
        $this->assertEqualsWithDelta(43.7682899, $location->getLatitude(), 0.0001);
        $this->assertEqualsWithDelta(11.2556199, $location->getLongitude(), 0.0001);

        $this->assertTrue($entry->has('link'));
        $link = $entry->get('link', null, false);
        $this->assertLink('SQOIQ1rZMQQUeyoyGiEUq', 'Asset', $link);

        $this->assertTrue($entry->has('arrayOfLinks'));
        $arrayOfLinks = $entry->get('arrayOfLinks', null, false);
        $this->assertLink('5teS5mSVJ66qg6QOIY0SWI', 'Entry', $arrayOfLinks[0]);

        $this->assertTrue($entry->has('text'));
        $this->assertSame('Some text', $entry->get('text'));

        $this->assertTrue($entry->has('boolean'));
        $this->assertTrue($entry->get('boolean'));
    }

    /**
     * @vcr entry_partial_building_with_all_locales.json
     */
    public function testPartialBuildingWithAllLocales()
    {
        $client = $this->getClient('new');

        $query = (new Query())
            ->setLocale('*')
            ->setContentType('complexContentType')
            ->select(['fields.link', 'fields.location', 'fields.date', 'fields.arrayOfLinks'])
            ->where('sys.id', '5teS5mSVJ66qg6QOIY0SWI')
        ;
        /** @var Entry $entry */
        $entry = $client->getEntries($query)[0];

        $this->assertTrue($entry->has('date'));
        $this->assertSame('2017-12-31T22:00:00Z', (string) $entry->get('date'));

        $this->assertTrue($entry->has('location'));
        $location = $entry->get('location');
        $this->assertEqualsWithDelta(43.7682899, $location->getLatitude(), 0.0001);
        $this->assertEqualsWithDelta(11.2556199, $location->getLongitude(), 0.0001);

        $this->assertTrue($entry->has('link'));
        $link = $entry->get('link', null, false);
        $this->assertLink('SQOIQ1rZMQQUeyoyGiEUq', 'Asset', $link);

        $this->assertTrue($entry->has('arrayOfLinks'));
        $arrayOfLinks = $entry->get('arrayOfLinks', null, false);
        $this->assertLink('5teS5mSVJ66qg6QOIY0SWI', 'Entry', $arrayOfLinks[0]);

        $this->assertFalse($entry->has('text'));
        $this->assertFalse($entry->has('boolean'));

        $query = (new Query())
            ->setLocale('*')
            ->setContentType('complexContentType')
            ->select(['fields.text', 'fields.boolean'])
            ->where('sys.id', '5teS5mSVJ66qg6QOIY0SWI')
        ;
        $entry = $client->getEntries($query)[0];

        $this->assertTrue($entry->has('date'));
        $this->assertSame('2017-12-31T22:00:00Z', (string) $entry->get('date'));

        $this->assertTrue($entry->has('location'));
        $location = $entry->get('location');
        $this->assertEqualsWithDelta(43.7682899, $location->getLatitude(), 0.0001);
        $this->assertEqualsWithDelta(11.2556199, $location->getLongitude(), 0.0001);

        $this->assertTrue($entry->has('link'));
        $link = $entry->get('link', null, false);
        $this->assertLink('SQOIQ1rZMQQUeyoyGiEUq', 'Asset', $link);

        $this->assertTrue($entry->has('arrayOfLinks'));
        $arrayOfLinks = $entry->get('arrayOfLinks', null, false);
        $this->assertLink('5teS5mSVJ66qg6QOIY0SWI', 'Entry', $arrayOfLinks[0]);

        $this->assertTrue($entry->has('text'));
        $this->assertSame('Some text', $entry->get('text'));

        $this->assertTrue($entry->has('boolean'));
        $this->assertTrue($entry->get('boolean'));
    }

    /**
     * @vcr entry_partial_building_with_non_default_locale.json
     */
    public function testPartialBuildingWithNonDefaultLocale()
    {
        $client = $this->getClient('new');

        $query = (new Query())
            ->setContentType('complexContentType')
            ->select(['fields.link', 'fields.location', 'fields.date', 'fields.arrayOfLinks'])
            ->where('sys.id', '5teS5mSVJ66qg6QOIY0SWI')
            ->setLocale('it')
        ;
        $entry = $client->getEntries($query)[0];

        $this->assertTrue($entry->has('date'));
        $this->assertSame('2017-12-31T22:00:00Z', (string) $entry->get('date'));

        $this->assertTrue($entry->has('location'));
        $location = $entry->get('location');
        $this->assertEqualsWithDelta(43.7682899, $location->getLatitude(), 0.0001);
        $this->assertEqualsWithDelta(11.2556199, $location->getLongitude(), 0.0001);

        $this->assertTrue($entry->has('link'));
        $link = $entry->get('link', null, false);
        $this->assertLink('SQOIQ1rZMQQUeyoyGiEUq', 'Asset', $link);

        $this->assertTrue($entry->has('arrayOfLinks'));
        $arrayOfLinks = $entry->get('arrayOfLinks', null, false);
        $this->assertLink('5teS5mSVJ66qg6QOIY0SWI', 'Entry', $arrayOfLinks[0]);

        $this->assertFalse($entry->has('text'));
        $this->assertFalse($entry->has('boolean'));

        $query = (new Query())
            ->setContentType('complexContentType')
            ->select(['fields.text', 'fields.boolean'])
            ->where('sys.id', '5teS5mSVJ66qg6QOIY0SWI')
            ->setLocale('it')
        ;
        $entry = $client->getEntries($query)[0];

        $this->assertTrue($entry->has('date'));
        $this->assertSame('2017-12-31T22:00:00Z', (string) $entry->get('date'));

        $this->assertTrue($entry->has('location'));
        $location = $entry->get('location');
        $this->assertEqualsWithDelta(43.7682899, $location->getLatitude(), 0.0001);
        $this->assertEqualsWithDelta(11.2556199, $location->getLongitude(), 0.0001);

        $this->assertTrue($entry->has('link'));
        $link = $entry->get('link', null, false);
        $this->assertLink('SQOIQ1rZMQQUeyoyGiEUq', 'Asset', $link);

        $this->assertTrue($entry->has('arrayOfLinks'));
        $arrayOfLinks = $entry->get('arrayOfLinks', null, false);
        $this->assertLink('5teS5mSVJ66qg6QOIY0SWI', 'Entry', $arrayOfLinks[0]);

        $this->assertTrue($entry->has('text'));
        $this->assertSame('Del testo', $entry->get('text'));

        $this->assertTrue($entry->has('boolean'));
        $this->assertTrue($entry->get('boolean'));
    }

    /**
     * @vcr entry_non_default_locale_on_linked_entries.json
     */
    public function testNonDefaultLocaleOnLinkedEntries()
    {
        $client = $this->getClient('new');

        $entry = $client->getEntry('4SRm6VeGUwGIyEaCekO6es', 'it');

        $this->assertSame('it', $entry->getLocale());
        $this->assertSame('it', $entry->getSystemProperties()->getLocale());

        /** @var Entry[] $related */
        $related = $entry->get('related');
        $this->assertCount(2, $related);

        foreach ($related as $relatedEntry) {
            $this->assertSame('it', $relatedEntry->getLocale());
            $this->assertSame('it', $relatedEntry->getSystemProperties()->getLocale());
        }

        $entry = $client->getEntry('4SRm6VeGUwGIyEaCekO6es', '*');

        $this->assertSame('en-US', $entry->getLocale());
        $this->assertNull($entry->getSystemProperties()->getLocale());

        $this->assertSame('Building', $entry->get('title', 'en-US'));
        $this->assertSame('Edificio', $entry->get('title', 'it'));

        // By not specifing the locale, the entry should use its current setting,
        // and in this case that "locale=*"
        /** @var Entry[] $related */
        $related = $entry->get('related');
        $this->assertCount(2, $related);

        foreach ($related as $relatedEntry) {
            $this->assertSame('en-US', $relatedEntry->getLocale());
            $this->assertNull($relatedEntry->getSystemProperties()->getLocale());
        }

        /** @var Entry[] $related */
        $related = $entry->get('related', 'it');
        $this->assertCount(2, $related);

        foreach ($related as $relatedEntry) {
            $this->assertSame('it', $relatedEntry->getLocale());
            $this->assertSame('it', $relatedEntry->getSystemProperties()->getLocale());
        }

        // There should be 7 API calls:
        // 1) Fetch main entry with locale "it"
        // 2) Fetch space
        // 3) Fetch locales
        // 4) Fetch content type
        // 5) Fetch related entries with locale "it"
        // 6) Fetch main entry with locale "*"
        // 7) Fetch related entries with locale "*"
        //
        // There should *not* be another call for related entries with locale "it",
        // as the resource pool should have those cached already.
        $this->assertCount(7, $client->getMessages());
    }

    /**
     * @vcr entry_link_resolver_only_fetches_missing_entries_with_default_locale.json
     */
    public function testLinkResolverOnlyFetchesMissingEntriesWithDefaultLocale()
    {
        $client = $this->getClient('new');
        $resourcePool = $client->getResourcePool();

        $entry = $client->getEntry('4SRm6VeGUwGIyEaCekO6es');

        $this->assertTrue($resourcePool->has('Entry', '4SRm6VeGUwGIyEaCekO6es', [
            'locale' => 'en-US',
        ]));
        $this->assertFalse($resourcePool->has('Entry', '2vATHvqCV2e0MoakIk42s', [
            'locale' => 'en-US',
        ]));
        $this->assertFalse($resourcePool->has('Entry', '4mJOqrfVEQWCs8iIYU4qkG', [
            'locale' => 'en-US',
        ]));

        // This is just to preload the entry
        $client->getEntry('2vATHvqCV2e0MoakIk42s');

        $this->assertTrue($resourcePool->has('Entry', '4SRm6VeGUwGIyEaCekO6es', [
            'locale' => 'en-US',
        ]));
        $this->assertTrue($resourcePool->has('Entry', '2vATHvqCV2e0MoakIk42s', [
            'locale' => 'en-US',
        ]));
        $this->assertFalse($resourcePool->has('Entry', '4mJOqrfVEQWCs8iIYU4qkG', [
            'locale' => 'en-US',
        ]));

        // First entry, space, locales, content type, second entry
        $this->assertCount(5, $client->getMessages());

        // This will trigger the fetching of the missing entry
        $entry->get('related');
        // First entry, space, locales, content type, second entry, third entry
        $this->assertCount(6, $client->getMessages());

        $this->assertTrue($resourcePool->has('Entry', '4SRm6VeGUwGIyEaCekO6es', [
            'locale' => 'en-US',
        ]));
        $this->assertTrue($resourcePool->has('Entry', '2vATHvqCV2e0MoakIk42s', [
            'locale' => 'en-US',
        ]));
        $this->assertTrue($resourcePool->has('Entry', '4mJOqrfVEQWCs8iIYU4qkG', [
            'locale' => 'en-US',
        ]));

        $message = $client->getMessages()[5];
        $this->assertSame(
            $this->getHost().'spaces/88dyiqcr7go8/environments/master/entries?sys.id%5Bin%5D=4mJOqrfVEQWCs8iIYU4qkG&locale=en-US',
            (string) $message->getRequest()->getUri()
        );
    }

    /**
     * @vcr entry_link_resolver_does_not_fetch_preloaded_entries_with_default_locale.json
     */
    public function testLinkResolverDoesNotFetchPreloadedEntriesWithDefaultLocale()
    {
        $client = $this->getClient('new');
        $resourcePool = $client->getResourcePool();

        $entry = $client->getEntry('4SRm6VeGUwGIyEaCekO6es');

        $this->assertTrue($resourcePool->has('Entry', '4SRm6VeGUwGIyEaCekO6es', [
            'locale' => 'en-US',
        ]));
        $this->assertFalse($resourcePool->has('Entry', '2vATHvqCV2e0MoakIk42s', [
            'locale' => 'en-US',
        ]));
        $this->assertFalse($resourcePool->has('Entry', '4mJOqrfVEQWCs8iIYU4qkG', [
            'locale' => 'en-US',
        ]));

        // This is just to preload the entries
        $client->getEntry('2vATHvqCV2e0MoakIk42s');
        $client->getEntry('4mJOqrfVEQWCs8iIYU4qkG');

        $this->assertTrue($resourcePool->has('Entry', '4SRm6VeGUwGIyEaCekO6es', [
            'locale' => 'en-US',
        ]));
        $this->assertTrue($resourcePool->has('Entry', '2vATHvqCV2e0MoakIk42s', [
            'locale' => 'en-US',
        ]));
        $this->assertTrue($resourcePool->has('Entry', '4mJOqrfVEQWCs8iIYU4qkG', [
            'locale' => 'en-US',
        ]));

        // First entry, space, locales, content type, second entry, third entry
        $this->assertCount(6, $client->getMessages());

        // This should not trigger further API calls
        $entry->get('related');
        // First entry, space, locales, content type, second entry, third entry
        $this->assertCount(6, $client->getMessages());

        $this->assertSame(
            $this->getHost().'spaces/88dyiqcr7go8/environments/master/entries/2vATHvqCV2e0MoakIk42s',
            (string) $client->getMessages()[4]->getRequest()->getUri()
        );
        $this->assertSame(
            $this->getHost().'spaces/88dyiqcr7go8/environments/master/entries/4mJOqrfVEQWCs8iIYU4qkG',
            (string) $client->getMessages()[5]->getRequest()->getUri()
        );
    }

    /**
     * @vcr entry_link_resolver_only_fetches_missing_entries_with_non_default_locale.json
     */
    public function testLinkResolverOnlyFetchesMissingEntriesWithNonDefaultLocale()
    {
        $client = $this->getClient('new');
        $resourcePool = $client->getResourcePool();

        $entry = $client->getEntry('4SRm6VeGUwGIyEaCekO6es', 'it');

        $this->assertTrue($resourcePool->has('Entry', '4SRm6VeGUwGIyEaCekO6es', [
            'locale' => 'it',
        ]));
        $this->assertFalse($resourcePool->has('Entry', '2vATHvqCV2e0MoakIk42s', [
            'locale' => 'it',
        ]));
        $this->assertFalse($resourcePool->has('Entry', '4mJOqrfVEQWCs8iIYU4qkG', [
            'locale' => 'it',
        ]));

        // This is just to preload the entry
        $client->getEntry('2vATHvqCV2e0MoakIk42s', 'it');

        $this->assertTrue($resourcePool->has('Entry', '4SRm6VeGUwGIyEaCekO6es', [
            'locale' => 'it',
        ]));
        $this->assertTrue($resourcePool->has('Entry', '2vATHvqCV2e0MoakIk42s', [
            'locale' => 'it',
        ]));
        $this->assertFalse($resourcePool->has('Entry', '4mJOqrfVEQWCs8iIYU4qkG', [
            'locale' => 'it',
        ]));

        // First entry, space, locales, content type, second entry
        $this->assertCount(5, $client->getMessages());

        // This will trigger the fetching of the missing entry
        $entry->get('related', 'it');
        // First entry, space, locales, content type, second entry, third entry
        $this->assertCount(6, $client->getMessages());

        $this->assertTrue($resourcePool->has('Entry', '4SRm6VeGUwGIyEaCekO6es', [
            'locale' => 'it',
        ]));
        $this->assertTrue($resourcePool->has('Entry', '2vATHvqCV2e0MoakIk42s', [
            'locale' => 'it',
        ]));
        $this->assertTrue($resourcePool->has('Entry', '4mJOqrfVEQWCs8iIYU4qkG', [
            'locale' => 'it',
        ]));

        $message = $client->getMessages()[5];
        $this->assertSame(
            $this->getHost().'spaces/88dyiqcr7go8/environments/master/entries?sys.id%5Bin%5D=4mJOqrfVEQWCs8iIYU4qkG&locale=it',
            (string) $message->getRequest()->getUri()
        );
    }

    /**
     * @vcr entry_link_resolver_does_not_fetch_preloaded_entries_with_non_default_locale.json
     */
    public function testLinkResolverDoesNotFetchPreloadedEntriesWithNonDefaultLocale()
    {
        $client = $this->getClient('new');
        $resourcePool = $client->getResourcePool();

        $entry = $client->getEntry('4SRm6VeGUwGIyEaCekO6es', 'it');

        $this->assertTrue($resourcePool->has('Entry', '4SRm6VeGUwGIyEaCekO6es', [
            'locale' => 'it',
        ]));
        $this->assertFalse($resourcePool->has('Entry', '2vATHvqCV2e0MoakIk42s', [
            'locale' => 'it',
        ]));
        $this->assertFalse($resourcePool->has('Entry', '4mJOqrfVEQWCs8iIYU4qkG', [
            'locale' => 'it',
        ]));

        // This is just to preload the entries
        $client->getEntry('2vATHvqCV2e0MoakIk42s', 'it');
        $client->getEntry('4mJOqrfVEQWCs8iIYU4qkG', 'it');

        $this->assertTrue($resourcePool->has('Entry', '4SRm6VeGUwGIyEaCekO6es', [
            'locale' => 'it',
        ]));
        $this->assertTrue($resourcePool->has('Entry', '2vATHvqCV2e0MoakIk42s', [
            'locale' => 'it',
        ]));
        $this->assertTrue($resourcePool->has('Entry', '4mJOqrfVEQWCs8iIYU4qkG', [
            'locale' => 'it',
        ]));

        // First entry, space, locales, content type, second entry, third entry
        $this->assertCount(6, $client->getMessages());

        // This should not trigger further API calls
        $entry->get('related');
        // First entry, space, locales, content type, second entry, third entry
        $this->assertCount(6, $client->getMessages());

        $this->assertSame(
            $this->getHost().'spaces/88dyiqcr7go8/environments/master/entries/2vATHvqCV2e0MoakIk42s?locale=it',
            (string) $client->getMessages()[4]->getRequest()->getUri()
        );
        $this->assertSame(
            $this->getHost().'spaces/88dyiqcr7go8/environments/master/entries/4mJOqrfVEQWCs8iIYU4qkG?locale=it',
            (string) $client->getMessages()[5]->getRequest()->getUri()
        );
    }

    /**
     * @vcr entry_link_resolver_only_fetches_missing_entries_with_all_locales.json
     */
    public function testLinkResolverOnlyFetchesMissingEntriesWithAllLocales()
    {
        $client = $this->getClient('new');
        $resourcePool = $client->getResourcePool();

        $entry = $client->getEntry('4SRm6VeGUwGIyEaCekO6es', '*');

        $this->assertTrue($resourcePool->has('Entry', '4SRm6VeGUwGIyEaCekO6es', [
            'locale' => '*',
        ]));
        $this->assertFalse($resourcePool->has('Entry', '2vATHvqCV2e0MoakIk42s', [
            'locale' => '*',
        ]));
        $this->assertFalse($resourcePool->has('Entry', '4mJOqrfVEQWCs8iIYU4qkG', [
            'locale' => '*',
        ]));

        // This is just to preload the entry
        $client->getEntry('2vATHvqCV2e0MoakIk42s', '*');

        $this->assertTrue($resourcePool->has('Entry', '4SRm6VeGUwGIyEaCekO6es', [
            'locale' => '*',
        ]));
        $this->assertTrue($resourcePool->has('Entry', '2vATHvqCV2e0MoakIk42s', [
            'locale' => '*',
        ]));
        $this->assertFalse($resourcePool->has('Entry', '4mJOqrfVEQWCs8iIYU4qkG', [
            'locale' => '*',
        ]));

        // First entry, space, locales, content type, second entry
        $this->assertCount(5, $client->getMessages());

        // This will trigger the fetching of the missing entry
        $entry->get('related');
        // First entry, space, locales, content type, second entry, third entry
        $this->assertCount(6, $client->getMessages());

        $this->assertTrue($resourcePool->has('Entry', '4SRm6VeGUwGIyEaCekO6es', [
            'locale' => '*',
        ]));
        $this->assertTrue($resourcePool->has('Entry', '2vATHvqCV2e0MoakIk42s', [
            'locale' => '*',
        ]));
        $this->assertTrue($resourcePool->has('Entry', '4mJOqrfVEQWCs8iIYU4qkG', [
            'locale' => '*',
        ]));

        $message = $client->getMessages()[5];
        $this->assertSame(
            $this->getHost().'spaces/88dyiqcr7go8/environments/master/entries?sys.id%5Bin%5D=4mJOqrfVEQWCs8iIYU4qkG&locale=%2A',
            (string) $message->getRequest()->getUri()
        );
    }

    /**
     * @vcr entry_link_resolver_does_not_fetch_preloaded_entries_with_all_locales.json
     */
    public function testLinkResolverDoesNotFetchPreloadedEntriesWithAllLocales()
    {
        $client = $this->getClient('new');
        $resourcePool = $client->getResourcePool();

        $entry = $client->getEntry('4SRm6VeGUwGIyEaCekO6es', '*');

        $this->assertTrue($resourcePool->has('Entry', '4SRm6VeGUwGIyEaCekO6es', [
            'locale' => '*',
        ]));
        $this->assertFalse($resourcePool->has('Entry', '2vATHvqCV2e0MoakIk42s', [
            'locale' => '*',
        ]));
        $this->assertFalse($resourcePool->has('Entry', '4mJOqrfVEQWCs8iIYU4qkG', [
            'locale' => '*',
        ]));

        // This is just to preload the entries
        $client->getEntry('2vATHvqCV2e0MoakIk42s', '*');
        $client->getEntry('4mJOqrfVEQWCs8iIYU4qkG', '*');

        $this->assertTrue($resourcePool->has('Entry', '4SRm6VeGUwGIyEaCekO6es', [
            'locale' => '*',
        ]));
        $this->assertTrue($resourcePool->has('Entry', '2vATHvqCV2e0MoakIk42s', [
            'locale' => '*',
        ]));
        $this->assertTrue($resourcePool->has('Entry', '4mJOqrfVEQWCs8iIYU4qkG', [
            'locale' => '*',
        ]));

        // First entry, space, locales, content type, second entry, third entry
        $this->assertCount(6, $client->getMessages());

        // This should not trigger further API calls
        $entry->get('related');
        // First entry, space, locales, content type, second entry, third entry
        $this->assertCount(6, $client->getMessages());

        $this->assertSame(
            $this->getHost().'spaces/88dyiqcr7go8/environments/master/entries/2vATHvqCV2e0MoakIk42s?locale=%2A',
            (string) $client->getMessages()[4]->getRequest()->getUri()
        );
        $this->assertSame(
            $this->getHost().'spaces/88dyiqcr7go8/environments/master/entries/4mJOqrfVEQWCs8iIYU4qkG?locale=%2A',
            (string) $client->getMessages()[5]->getRequest()->getUri()
        );
    }

    /**
     * @vcr entry_test_get_lots_of_entries.json
     */
    public function testGetLotsOfEntries()
    {
        $client = $this->getClient('new');
        $resourcePool = $client->getResourcePool();

        for ($i = 0; $i < 100; ++$i) {
            $entry = $client->getEntry('4mJOqrfVEQWCs8iIYU4qkG', '*');
        }

        $this->assertTrue($resourcePool->has('Entry', '4mJOqrfVEQWCs8iIYU4qkG', [
            'locale' => '*',
        ]));
    }
}

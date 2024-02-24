<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\E2E;

use Contentful\Delivery\Query;
use Contentful\Delivery\Resource\Asset;
use Contentful\Delivery\Resource\Entry;
use Contentful\Tests\Delivery\TestCase;

class EntryFilterTest extends TestCase
{
    /**
     * @vcr entry_filter_all.json
     */
    public function testAll()
    {
        $client = $this->getClient('default');

        // entries?content_type={content_type}&{attribute}%5Bnin%5D={value}
        $query = (new Query())
            ->setContentType('cat')
            ->where('sys.id[nin]', ['nyancat'])
        ;
        $entries = $client->getEntries($query);
        /** @var Entry $entry */
        foreach ($entries as $entry) {
            if ('nyancat' === $entry->getId()) {
                $this->fail('Entry with ID "nyancat" was fetched even though it should not have.');
            }
        }

        // entries?content_type={content_type}&{attribute}%5Bexists%5D={value}
        $query = (new Query())
            ->setContentType('cat')
            ->where('fields.name[exists]', 'true')
        ;
        $entries = $client->getEntries($query);
        $this->assertCount(3, $entries);

        // entries?{attribute}%5Blte%5D={value}
        $query = (new Query())
            ->where('sys.createdAt[lte]', '2013-06-27T22:46:19.513Z')
        ;
        $entries = $client->getEntries($query);
        $this->assertCount(1, $entries);

        // entries?content_type={content_type}&fields.{field_id}%5Bmatch%5D={value}
        $query = (new Query())
            ->setContentType('cat')
            ->where('fields.name[match]', 'cat')
        ;
        $entries = $client->getEntries($query);
        $this->assertCount(2, $entries);

        // entries?fields.center%5Bnear%5D={coordinate}&content_type={content_type}
        $query = (new Query())
            ->setContentType('1t9IbcfdCk6m04uISSsaIK')
            ->where('fields.center[near]', '51.508530,-0.076132')
        ;
        $entries = $client->getEntries($query);
        $this->assertSame('London', $entries[0]->get('name'));

        // entries?fields.center%5Bwithin%5D={rectangle}&content_type={content_type}
        $query = (new Query())
            ->setContentType('1t9IbcfdCk6m04uISSsaIK')
            ->where('fields.center[within]', '48,-1,52,0')
        ;
        $entries = $client->getEntries($query);
        $this->assertSame('London', $entries[0]->get('name'));

        // entries?order={attribute}
        $query = (new Query())
            ->orderBy('sys.createdAt')
        ;
        $entries = $client->getEntries($query);
        $this->assertSame('nyancat', $entries[0]->getId());

        // entries?order=-{attribute}
        $query = (new Query())
            ->orderBy('sys.createdAt', true)
        ;
        $entries = $client->getEntries($query);
        $this->assertSame('San Francisco', $entries[0]->get('name'));

        // entries?order={attribute},{attribute2}
        $query = (new Query())
            ->orderBy('sys.createdAt,sys.revision')
        ;
        $entries = $client->getEntries($query);
        $this->assertSame('nyancat', $entries[0]->getId());

        // entries?limit={value}
        $query = (new Query())
            ->setLimit(1)
        ;
        $entries = $client->getEntries($query);
        $this->assertCount(1, $entries);

        // entries?skip={value}
        $query = (new Query())
            ->setSkip(1)
        ;
        $entries = $client->getEntries($query);
        $this->assertInstanceOf(Entry::class, $entries[0]);

        // entries?content_type={content_type}&fields.{linking_field}.sys.id={target_entry_id}
        $query = (new Query())
            ->setContentType('cat')
            ->where('fields.bestFriend.sys.id', 'happycat')
        ;
        $entries = $client->getEntries($query);
        $this->assertCount(1, $entries);
        $this->assertSame('nyancat', $entries[0]->getId());

        // assets?mimetype_group={mimetype_group}
        $query = (new Query())
            ->setMimeTypeGroup('image')
        ;
        $assets = $client->getAssets($query);
        $this->assertCount(4, $assets);

        // entries?include={value}
        // Using include=0 means that every link will have to be fetched from the API
        // so we check that the client has made another query for fetching the happy cat entry.
        // We create a new client because the previous one will have entries and assets already cached locally.
        $client = $this->getClient('default');
        $query = (new Query())
            ->setInclude(0)
        ;
        $entries = $client->getEntries($query);
        $currentTotal = \count($client->getMessages());
        /** @var Entry $entry */
        foreach ($entries as $entry) {
            if ('nyancat' === $entry->getId()) {
                /** @var Asset $image */
                $image = $entry->get('image');
                $this->assertInstanceOf(Asset::class, $image);
                $this->assertSame('nyancat', $image->getId());
                break;
            }
        }

        $this->assertCount($currentTotal + 1, $client->getMessages());
    }
}

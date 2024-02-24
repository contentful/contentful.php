<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\E2E;

use Contentful\Delivery\Resource\Asset;
use Contentful\Delivery\Resource\DeletedEntry;
use Contentful\Delivery\Resource\Entry;
use Contentful\Delivery\Synchronization\Query;
use Contentful\Delivery\Synchronization\Result;
use Contentful\Tests\Delivery\TestCase;

class SyncTest extends TestCase
{
    /**
     * @vcr sync_basic.json
     */
    public function testBasic()
    {
        $client = $this->getClient('default');

        $manager = $client->getSynchronizationManager();

        $result = $manager->startSync();

        $this->assertInstanceOf(Result::class, $result);
        $this->assertGreaterThan(40, mb_strlen($result->getToken()));
        $this->assertTrue($result->isDone());

        $items = $result->getItems();
        $this->assertInstanceOf(Entry::class, $items[0]);

        $result2 = $manager->continueSync($result);

        $this->assertInstanceOf(Result::class, $result2);
        $this->assertTrue($result2->isDone());
    }

    /**
     * @vcr sync_preview.json
     */
    public function testPreview()
    {
        $this->skipIfApiCoverage();

        $manager = $this->getClient('default_preview')
            ->getSynchronizationManager()
        ;

        $result = $manager->startSync();

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isDone());
    }

    /**
     * @vcr sync_preview_continue.json
     */
    public function testPreviewContinue()
    {
        $this->skipIfApiCoverage();

        $this->expectException(\RuntimeException::class);

        $manager = $this->getClient('default_preview')
            ->getSynchronizationManager()
        ;

        $result = $manager->startSync();
        $manager->continueSync($result);
    }

    /**
     * @vcr sync_full.json
     */
    public function testFull()
    {
        $this->skipIfApiCoverage();

        $manager = $this->getClient('default')
            ->getSynchronizationManager()
        ;

        $results = [];
        foreach ($manager->sync() as $result) {
            $results[] = $result;
        }

        $this->assertCount(1, $results);
        $this->assertTrue($result->isDone());
        $this->assertGreaterThan(40, mb_strlen($result->getToken()));
    }

    /**
     * @vcr sync_type.json
     */
    public function testType()
    {
        $client = $this->getClient('default');
        $manager = $client->getSynchronizationManager();

        $query = (new Query())
            ->setType('Asset')
        ;
        $result = $manager->startSync($query);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertGreaterThan(40, mb_strlen($result->getToken()));
        $this->assertTrue($result->isDone());

        $items = $result->getItems();
        $this->assertInstanceOf(Asset::class, $items[0]);

        $query = (new Query())
            ->setType('Entry')
            ->setContentType('cat')
        ;
        $result = $manager->startSync($query);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertGreaterThan(40, mb_strlen($result->getToken()));
        $this->assertTrue($result->isDone());

        $items = $result->getItems();
        $this->assertInstanceOf(Entry::class, $items[0]);
        $this->assertSame('cat', $items[0]->getSystemProperties()->getContentType()->getId());
    }

    /**
     * @vcr deleted_entry_with_sync_api.json
     */
    public function testDeletedEntryWithSyncApi()
    {
        $client = $this->getClient('new');
        $manager = $client->getSynchronizationManager();

        $token = 'w5ZGw6JFwqZmVcKsE8Kow4grw45QdyYxwpvDhWzCrMOCZcO4XsK5wqfDmUfDrsOXM8KwH8Knw4VxARRWwpLDiVBIPMOnS8K8wpDDvXbCli8vGsOtaykzAU3CmcKhwrXCqsOow74-DcO0w78XNMOsJsOPw4gfai0';
        $result = $manager->continueSync($token);
        /** @var DeletedEntry $entry */
        $entry = $result->getItems()[0];

        $this->assertSame('1snrLzDMcQjgAkmYI91S9S', $entry->getId());

        $contentType = $entry->getContentType();
        $this->assertSame('__DeletedEntryContentType', $contentType->getId());
        $this->assertSame('Deleted Entry', $contentType->getName());
    }
}

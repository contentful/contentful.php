<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery\E2E;

use Contentful\Delivery\Resource\Entry;
use Contentful\Delivery\Synchronization\Result;
use Contentful\Tests\Delivery\TestCase;

class SyncTest extends TestCase
{
    /**
     * @vcr e2e_sync_basic.json
     */
    public function testBasicSync()
    {
        $client = $this->getClient('cfexampleapi');

        $manager = $client->getSynchronizationManager();

        $result = $manager->startSync();

        $this->assertInstanceOf(Result::class, $result);
        $this->assertGreaterThan(40, \mb_strlen($result->getToken()));
        $this->assertTrue($result->isDone());

        $items = $result->getItems();
        $this->assertInstanceOf(Entry::class, $items[0]);

        $result2 = $manager->continueSync($result);

        $this->assertInstanceOf(Result::class, $result2);
        $this->assertTrue($result2->isDone());
    }

    /**
     * @requires API no-coverage-proxy
     * @vcr e2e_sync_preview.json
     */
    public function testPreviewSync()
    {
        $manager = $this->getClient('cfexampleapi_preview')
            ->getSynchronizationManager();

        $result = $manager->startSync();

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isDone());
    }

    /**
     * @requires API no-coverage-proxy
     * @vcr e2e_sync_preview_continue.json
     * @expectedException \RuntimeException
     */
    public function testPreviewSyncContinue()
    {
        $manager = $this->getClient('cfexampleapi_preview')
            ->getSynchronizationManager();

        $result = $manager->startSync();
        $manager->continueSync($result);
    }

    /**
     * @requires API no-coverage-proxy
     * @vcr e2e_sync_full.json
     */
    public function testSyncFull()
    {
        $manager = $this->getClient('cfexampleapi')
            ->getSynchronizationManager();

        $results = [];
        foreach ($manager->sync() as $result) {
            $results[] = $result;
        }

        $this->assertSame(2, \count($results));
        $this->assertTrue($result->isDone());
        $this->assertGreaterThan(40, \mb_strlen($result->getToken()));
    }
}

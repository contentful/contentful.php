<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Client;
use Contentful\Delivery\DynamicEntry;
use Contentful\Delivery\Synchronization\Result;
use Contentful\Tests\Unit\Delivery\DynamicEntryTest;

class SyncTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @vcr e2e_sync_basic.json
     */
    public function testBasicSync()
    {
        $manager = (new Client('b4c0n73n7fu1', 'cfexampleapi'))
            ->getSynchronizationManager();

        $result = $manager->startSync();

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals('w5ZGw6JFwqZmVcKsE8Kow4grw45QdybCnV_Cg8OASMKpwo1UY8K8bsKFwqJrw7DDhcKnM2RDOVbDt1E-wo7CnDjChMKKGsK1wrzCrBzCqMOpZAwOOcOvCcOAwqHDv0XCiMKaOcOxZA8BJUzDr8K-wo1lNx7DnHE', $result->getToken());
        $this->assertTrue($result->isDone());

        $items = $result->getItems();
        $this->assertInstanceOf(DynamicEntry::class, $items[0]);

        $result2 = $manager->continueSync($result);

        $this->assertInstanceOf(Result::class, $result2);
        $this->assertTrue($result2->isDone());
    }

    /**
     * @vcr e2e_sync_preview.json
     */
    public function testPreviewSync()
    {
        $manager = (new Client('e5e8d4c5c122cf28fc1af3ff77d28bef78a3952957f15067bbc29f2f0dde0b50', 'cfexampleapi', true))
            ->getSynchronizationManager();

        $result = $manager->startSync();

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isDone());
    }
}

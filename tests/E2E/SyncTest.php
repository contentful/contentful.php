<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Client;
use Contentful\Delivery\Synchronization\Result;

class SyncTest extends \PHPUnit_Framework_TestCase
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
     * @vcr e2e_sync_basic.json
     */
    public function testBasicSync()
    {
        $mananger = $this->client->getSynchronizationManager();

        $result = $mananger->startSync();

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals('w5ZGw6JFwqZmVcKsE8Kow4grw45QdybCnV_Cg8OASMKpwo1UY8K8bsKFwqJrw7DDhcKnM2RDOVbDt1E-wo7CnDjChMKKGsK1wrzCrBzCqMOpZAwOOcOvCcOAwqHDv0XCiMKaOcOxZA8BJUzDr8K-wo1lNx7DnHE', $result->getToken());
        $this->assertTrue($result->isDone());

        $result2 = $mananger->continueSync($result);

        $this->assertInstanceOf(Result::class, $result2);
        $this->assertTrue($result2->isDone());
    }
}

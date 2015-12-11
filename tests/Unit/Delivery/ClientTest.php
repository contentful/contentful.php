<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\Delivery;

use Contentful\Delivery\Client;
use Contentful\Delivery\Synchronization\Manager;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testIsPreview()
    {
        $client = new Client('b4c0n73n7fu1', 'cfexampleapi');

        $this->assertFalse($client->isPreview());
    }

    public function testGetSynchronizationManager()
    {
        $client = new Client('b4c0n73n7fu1', 'cfexampleapi');

        $this->assertInstanceOf(Manager::class, $client->getSynchronizationManager());
    }

    public function testIsPreviewPreview()
    {
        $client = new Client('b4c0n73n7fu1', 'cfexampleapi', true);

        $this->assertTrue($client->isPreview());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetSynchronizationPreview()
    {
        $client = new Client('b4c0n73n7fu1', 'cfexampleapi', true);

        $client->getSynchronizationManager();
    }
}

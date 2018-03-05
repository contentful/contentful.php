<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery\Unit;

use Contentful\Delivery\Client;
use Contentful\Delivery\Synchronization\Manager;
use Contentful\Tests\Delivery\TestCase;

class ClientTest extends TestCase
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
}

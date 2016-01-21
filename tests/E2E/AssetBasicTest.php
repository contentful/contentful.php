<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Client;
use Contentful\ResourceArray;
use Contentful\Delivery\Asset;

class AssetBasicTest extends \PHPUnit_Framework_TestCase
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
     * @vcr e2e_asset_get_all.json
     */
    public function testGetAll()
    {
        $assets = $this->client->getAssets();

        $this->assertInstanceOf(ResourceArray::class, $assets);
    }

    /**
     * @vcr e2e_asset_get_one.json
     */
    public function testGetOne()
    {
        $asset = $this->client->getAsset('nyancat');

        $this->assertInstanceOf(Asset::class, $asset);
        $this->assertEquals('nyancat', $asset->getId());
    }
}

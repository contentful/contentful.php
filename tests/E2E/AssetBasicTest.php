<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Client;
use Contentful\Delivery\Query;
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
     * @vcr e2e_asset_get_all_locale_all.json
     */
    public function testGetAll()
    {
        $query = (new Query())
            ->setLocale('*');
        $assets = $this->client->getAssets($query);

        $this->assertInstanceOf(ResourceArray::class, $assets);
    }

    /**
     * @vcr e2e_asset_get_all_locale_default.json
     */
    public function testGetAllSingleLocale()
    {
        $assets = $this->client->getAssets();

        $this->assertInstanceOf(ResourceArray::class, $assets);
    }

    /**
     * @vcr e2e_asset_get_one_locale_all.json
     */
    public function testGetOne()
    {
        $asset = $this->client->getAsset('nyancat', '*');

        $this->assertInstanceOf(Asset::class, $asset);
        $this->assertEquals('nyancat', $asset->getId());
    }

    /**
     * @vcr e2e_asset_get_one_locale_default.json
     */
    public function testGetOneSingleLocale()
    {
        $asset = $this->client->getAsset('nyancat');

        $this->assertInstanceOf(Asset::class, $asset);
        $this->assertEquals('nyancat', $asset->getId());
    }
}

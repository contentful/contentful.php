<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Core\File\ImageFile;
use Contentful\Core\Resource\ResourceArray;
use Contentful\Delivery\Query;
use Contentful\Delivery\Resource\Asset;
use Contentful\Tests\DeliveryEnd2EndTestCase;

class AssetTest extends DeliveryEnd2EndTestCase
{
    /**
     * @vcr e2e_asset_get_all_locale_all.json
     */
    public function testGetAll()
    {
        $client = $this->getClient('cfexampleapi');

        $query = (new Query())
            ->setLocale('*');
        $assets = $client->getAssets($query);

        $this->assertInstanceOf(ResourceArray::class, $assets);
    }

    /**
     * @vcr e2e_asset_get_all_locale_default.json
     */
    public function testGetAllSingleLocale()
    {
        $client = $this->getClient('cfexampleapi');

        $assets = $client->getAssets();

        $this->assertInstanceOf(ResourceArray::class, $assets);
    }

    /**
     * @vcr e2e_asset_get_one_locale_all.json
     */
    public function testGetOne()
    {
        $client = $this->getClient('cfexampleapi');

        $asset = $client->getAsset('nyancat', '*');

        $this->assertInstanceOf(Asset::class, $asset);
        $this->assertSame('nyancat', $asset->getId());
        $this->assertInstanceOf(ImageFile::class, $asset->getFile());
    }

    /**
     * @vcr e2e_asset_get_one_locale_default.json
     */
    public function testGetOneSingleLocale()
    {
        $client = $this->getClient('cfexampleapi');

        $asset = $client->getAsset('nyancat');

        $this->assertInstanceOf(Asset::class, $asset);
        $this->assertSame('nyancat', $asset->getId());
    }
}

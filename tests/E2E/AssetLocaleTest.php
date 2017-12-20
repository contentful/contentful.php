<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Query;
use Contentful\File\File;
use Contentful\Tests\Delivery\End2EndTestCase;

class AssetLocaleTest extends End2EndTestCase
{
    /**
     * @vcr e2e_asset_included_locale.json
     */
    public function testIncludedAssetLocale()
    {
        $client = $this->getClient('88dyiqcr7go8');

        $query = (new Query())
            ->setInclude(1)
            ->where('sys.id', 'Kpwt1njxgAm04oQYyUScm')
            ->setLocale('es');

        $entry = $client->getEntries($query)[0];
        // This is to make sure that the retrieved asset has the locale code correctly initialized
        $this->assertSame('Ben Chang', $entry->getName());
        $this->assertSame('Señor', $entry->getJobTitle());

        $asset = $entry->getPicture();
        $this->assertSame('es', $asset->getLocale());
        $this->assertSame('ben-chang', $asset->getTitle());
        $this->assertSame('I AM A SPANISH GENIUS!', $asset->getDescription());

        // Tests that the fallback chain is working correctly.
        // Tested chain is es -> it -> en-US
        $this->assertNull($asset->getFile('it'));
        $this->assertNull($asset->getFile('en-US'));
        $this->assertInstanceOf(File::class, $asset->getFile());
        $this->assertInstanceOf(File::class, $asset->getFile('es'));
        $this->assertSame('//images.contentful.com/88dyiqcr7go8/SQOIQ1rZMQQUeyoyGiEUq/84b6aef287ed214b464114655f99bfa8/ben-chang.jpg', $asset->getFile('es')->getUrl());
    }
}

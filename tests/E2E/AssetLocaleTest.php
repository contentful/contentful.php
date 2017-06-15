<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Client;
use Contentful\Delivery\Query;

class AssetLocaleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client
     */
    private $client;

    public function setUp()
    {
        $this->client = new Client('668efbfd9e398181166dec5df5a500aded96dbca2916646a3c7ec37082a7b756', '88dyiqcr7go8');
    }

    /**
     * @vcr e2e_asset_included_locale.json
     */
    public function testIncludedAssetLocale()
    {
        $query = (new Query())
            ->setInclude(1)
            ->where('sys.id', 'Kpwt1njxgAm04oQYyUScm')
            ->setLocale('es');

        $entry = $this->client->getEntries($query)[0];
        // This is to make sure that the retrieved asset has the locale code correctly initialized
        $this->assertEquals('Ben Chang', $entry->getName());
        $this->assertEquals('Señor', $entry->getJobTitle());

        $asset = $entry->getPicture();
        $this->assertEquals('es', $asset->getLocale());
        $this->assertEquals('ben-chang', $asset->getTitle());
        $this->assertEquals('I AM A SPANISH GENIUS!', $asset->getDescription());
    }
}

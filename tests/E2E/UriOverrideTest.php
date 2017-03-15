<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Client;

class UriOverrideTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client
     */
    private $client;

    public function setUp()
    {
        $this->client = new Client('e5e8d4c5c122cf28fc1af3ff77d28bef78a3952957f15067bbc29f2f0dde0b50', 'cfexampleapi', false, null, [
            'uriOverride' => 'https://preview.contentful.com/'
        ]);
    }

    /**
     * @vcr e2e_uri_override.json
     */
    public function testBasicSync()
    {
        $space = $this->client->getSpace();

        $this->assertEquals('Contentful Example API', $space->getName());
    }
}

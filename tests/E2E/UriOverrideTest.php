<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Client;
use Contentful\Tests\Delivery\End2EndTestCase;

class UriOverrideTest extends End2EndTestCase
{
    /**
     * @requires API no-coverage-proxy
     * @vcr e2e_uri_override.json
     */
    public function testBasicSync()
    {
        $client = new Client('e5e8d4c5c122cf28fc1af3ff77d28bef78a3952957f15067bbc29f2f0dde0b50', 'cfexampleapi', false, null, [
            'uriOverride' => 'https://preview.contentful.com/',
        ]);
        $space = $client->getSpace();

        $this->assertSame('Contentful Example API', $space->getName());
    }
}

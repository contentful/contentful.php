<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery\E2E;

use Contentful\Delivery\Client;
use Contentful\Tests\Delivery\TestCase;

class UriOverrideTest extends TestCase
{
    /**
     * @requires API no-coverage-proxy
     * @vcr e2e_uri_override_without_trailing_slash.json
     */
    public function testOverrideWithoutTrailingSlash()
    {
        $client = new Client('e5e8d4c5c122cf28fc1af3ff77d28bef78a3952957f15067bbc29f2f0dde0b50', 'cfexampleapi', 'master', false, null, [
            'baseUri' => 'https://preview.contentful.com',
        ]);
        $space = $client->getSpace();

        $this->assertSame('Contentful Example API', $space->getName());
    }

    /**
     * @requires API no-coverage-proxy
     * @vcr e2e_uri_override_with_trailing_slash.json
     */
    public function testOverrideWithTrailingSlash()
    {
        $client = new Client('e5e8d4c5c122cf28fc1af3ff77d28bef78a3952957f15067bbc29f2f0dde0b50', 'cfexampleapi', 'master', false, null, [
            'baseUri' => 'https://preview.contentful.com/',
        ]);
        $space = $client->getSpace();

        $this->assertSame('Contentful Example API', $space->getName());
    }
}

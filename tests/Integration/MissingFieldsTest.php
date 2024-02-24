<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Integration;

use Contentful\Delivery\ClientOptions;
use Contentful\Tests\Delivery\TestCase;

class MissingFieldsTest extends TestCase
{
    /**
     * A field in an entry may be totally missing when it's not set in the UI.
     *
     * The test data is from the Product Catalogue Example space with which the issue was first noticed.
     */
    public function testMissingFieldsInEntries()
    {
        $client = $this->getJsonDecoderClient('rue07lqzt1co');

        $client->parseJson($this->getFixtureContent('space.json'));
        $client->parseJson($this->getFixtureContent('environment_entry.json'));
        $client->parseJson($this->getFixtureContent('content_type.json'));
        $entry = $client->parseJson($this->getFixtureContent('entry.json'));

        $this->assertNull($entry->getTwitter());
    }

    public function testMissingFieldsInAssets()
    {
        $client = $this->getJsonDecoderClient('rue07lqzt1co', 'master', ClientOptions::create()->usingPreviewApi());

        $client->parseJson($this->getFixtureContent('space.json'));
        $client->parseJson($this->getFixtureContent('environment_asset.json'));
        $asset = $client->parseJson($this->getFixtureContent('asset.json'));

        $this->assertNull($asset->getTitle());
        $this->assertNull($asset->getDescription());
        $this->assertNull($asset->getFile());
    }
}

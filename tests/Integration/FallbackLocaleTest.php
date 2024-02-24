<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Integration;

use Contentful\Tests\Delivery\TestCase;

/**
 * The fallback chain set up is the following
 * af -> zu-ZA -> en-US -> NULL
 * ne-NP -> NULL
 * bs_BA -> en-US -> NULL.
 */
class FallbackLocaleTest extends TestCase
{
    private function createClient()
    {
        $client = $this->getJsonDecoderClient('7dh3w86is8ls', 'master');

        $client->parseJson($this->getFixtureContent('space.json'));
        $client->parseJson($this->getFixtureContent('environment.json'));
        $client->parseJson($this->getFixtureContent('content_type.json'));

        return $client;
    }

    /**
     * No Nepali (ne-NP).
     */
    public function testNoNepali()
    {
        $client = $this->createClient();
        $entry = $client->parseJson($this->getFixtureContent('no_nepali.json'));

        $this->assertNull($entry->getTitle('ne-NP'));
    }

    /**
     * No Zulu (zu-ZA).
     */
    public function testNoZulu()
    {
        $client = $this->createClient();
        $entry = $client->parseJson($this->getFixtureContent('no_zulu.json'));

        $this->assertSame('no-zu-ZA', $entry->getTitle('zu-ZA'));
    }

    /**
     * No Afrikaans (af) and Zulu (zu-ZA).
     */
    public function testNoAfrikaansAndZulu()
    {
        $client = $this->createClient();
        $entry = $client->parseJson($this->getFixtureContent('no_afrikaans_and_zulu.json'));

        $this->assertSame('no-af-and-no-zu-ZA', $entry->getTitle('af'));
        $this->assertSame('no-af-and-no-zu-ZA', $entry->getTitle('zu-ZA'));
    }

    /**
     * No Bosnian (bs-BA).
     */
    public function testNoBosnian()
    {
        $client = $this->createClient();
        $entry = $client->parseJson($this->getFixtureContent('no_bosnian.json'));

        $this->assertSame('no-bs-BA', $entry->getTitle('bs-BA'));
    }

    /**
     * No Afrikaans (af).
     */
    public function testNoAfrikaans()
    {
        $client = $this->createClient();
        $entry = $client->parseJson($this->getFixtureContent('no_afrikaans.json'));

        $this->assertSame('zu-ZA', $entry->getTitle('af'));
    }
}

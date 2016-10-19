<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Integration;

use Contentful\Delivery\Client;

/**
 * The fallback chain set up is the following
 * af -> zu-ZA -> en-US -> NULL
 * ne-NP -> NULL
 * bs_BA -> en-US -> NULL
 */
class FallbackLocaleTest extends \PHPUnit_Framework_TestCase
{
    private function createClient()
    {
        $client = new Client('irrelevant', '7dh3w86is8ls');

        // First we load the Space
        $client->reviveJson('{"sys": {"type": "Space","id": "7dh3w86is8ls"},"name": "Fallback Locales","locales": [{"code": "en-US","default": true,"name": "U.S. English","fallbackCode": null},{"code": "af","default": false,"name": "Afrikaans","fallbackCode": "zu-ZA"},{"code": "zu-ZA","default": false,"name": "Zulu (South Africa)","fallbackCode": "en-US"},{"code": "bs-BA","default": false,"name": "Bosnian (Bosnia and Herzegovina)","fallbackCode": "en-US"},{"code": "ne-NP","default": false,"name": "Nepali (Nepal)","fallbackCode": null}]}');

        // then the Content Type
        $client->reviveJson('{"sys": {"space": {"sys": {"type": "Link","linkType": "Space","id": "7dh3w86is8ls"}},"id": "sampleType","type": "ContentType","createdAt": "2016-06-21T08:53:55.767Z","updatedAt": "2016-06-21T13:16:34.507Z","revision": 5},"displayField": "title","name": "Sample Type","description": "","fields": [{"id": "title","name": "title","type": "Symbol","localized": true,"required": true,"disabled": false,"omitted": false}]}');

        return $client;
    }

    /**
     * No ne-NP
     */
    public function testNoNeNP()
    {
        $client = $this->createClient();

        $entry = $client->reviveJson('{"sys": {"space": {"sys": {"type": "Link","linkType": "Space","id": "7dh3w86is8ls"}},"id": "no-ne-NP","type": "Entry","createdAt": "2016-07-04T15:58:21.612Z","updatedAt": "2016-07-04T15:58:21.612Z","revision": 1,"contentType": {"sys": {"type": "Link","linkType": "ContentType","id": "sampleType"}}},"fields": {"title": {"af": "af","bs-BA": "bs-BA","en-US": "no-ne-NP","zu-ZA": "zu-ZA"}}}');
        $this->assertNull($entry->getTitle('ne-NP'));
    }

    /**
     * No zu-ZA
     */
    public function testNoZuZA()
    {
        $client = $this->createClient();

        $entry = $client->reviveJson('{"sys": {"space": {"sys": {"type": "Link","linkType": "Space","id": "7dh3w86is8ls"}},"id": "no-zu-ZA","type": "Entry","createdAt": "2016-07-04T15:58:22.577Z","updatedAt": "2016-07-04T15:58:22.577Z","revision": 1,"contentType": {"sys": {"type": "Link","linkType": "ContentType","id": "sampleType"}}},"fields": {"title": {"af": "af","bs-BA": "bs-BA","en-US": "no-zu-ZA","ne-NP": "ne-NP"}}}');
        $this->assertEquals('no-zu-ZA', $entry->getTitle('zu-ZA'));
    }

    /**
     * No af and zu-ZA
     */
    public function testNoAfNoZuZa()
    {
        $client = $this->createClient();

        $entry = $client->reviveJson('{"sys": {"space": {"sys": {"type": "Link","linkType": "Space","id": "7dh3w86is8ls"}},"id": "no-af-and-no-zu-za","type": "Entry","createdAt": "2016-07-04T16:10:28.180Z","updatedAt": "2016-07-05T13:22:00.251Z","revision": 2,"contentType": {"sys": {"type": "Link","linkType": "ContentType","id": "sampleType"}}},"fields": {"title": {"bs-BA": "bs-BA","en-US": "no-af-and-no-zu-ZA","ne-NP": "ne-NP"}}}');

        $this->assertEquals('no-af-and-no-zu-ZA', $entry->getTitle('af'));
        $this->assertEquals('no-af-and-no-zu-ZA', $entry->getTitle('zu-ZA'));
    }

    /**
     * No bs-BA
     */
    public function testNoBsBa()
    {
        $client = $this->createClient();

        $entry = $client->reviveJson('{"sys": {"space": {"sys": {"type": "Link","linkType": "Space","id": "7dh3w86is8ls"}},"id": "no-bs-BA","type": "Entry","createdAt": "2016-07-04T15:58:20.529Z","updatedAt": "2016-07-04T15:58:20.529Z","revision": 1,"contentType": {"sys": {"type": "Link","linkType": "ContentType","id": "sampleType"}}},"fields": {"title": {"af": "af","en-US": "no-bs-BA","ne-NP": "ne-NP","zu-ZA": "zu-ZA"}}}');

        $this->assertEquals('no-bs-BA', $entry->getTitle('bs-BA'));
    }

    /**
     * No af
     */
    public function testNoAf()
    {
        $client = $this->createClient();

        $entry = $client->reviveJson('{"sys": {"space": {"sys": {"type": "Link","linkType": "Space","id": "7dh3w86is8ls"}},"id": "no-af","type": "Entry","createdAt": "2016-07-04T15:58:19.645Z","updatedAt": "2016-07-04T15:58:19.645Z","revision": 1,"contentType": {"sys": {"type": "Link","linkType": "ContentType","id": "sampleType"}}},"fields": {"title": {"bs-BA": "bs-BA","en-US": "no-af","ne-NP": "ne-NP","zu-ZA": "zu-ZA"}}}');

        $this->assertEquals('zu-ZA', $entry->getTitle('af'));
    }
}

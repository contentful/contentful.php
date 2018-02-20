<?php
/**
 * @copyright 2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Integration;

use Contentful\Delivery\Client;

/**
 * Contentful date fields can be either an ISO 8601 date or a Unix timestamp. The latter is much rarer and tested here.
 */
class UnixTimetampTest extends \PHPUnit_Framework_TestCase
{
    public function testDateAUnixTimestamp()
    {
        $client = new Client('irrelevant', 'rue07lqzt1co');

        // First we load the Space
        $client->reviveJson('{"sys":{"type":"Space","id":"rue07lqzt1co"},"name":"Product Catalog App","locales":[{"code":"en-US","default":true,"name":"U.S. English","fallbackCode":null}]}');

        // then the Content Type
        $client->reviveJson('{"fields":[{"name":"Some Date","id":"someDate","type":"Date","required":true}],"name":"Date","displayField": null,"sys":{"space":{"sys":{"type":"Link","linkType":"Space","id":"rue07lqzt1co"}},"type":"ContentType","id":"sFzTZbSuM8coEwygeUYes","revision":1,"createdAt":"2015-01-15T16:02:08.382Z","updatedAt":"2015-01-15T16:02:08.382Z"}}');

        // lastly, the entry
        $entry = $client->reviveJson('{"fields":{"someDate":{"en-US":"1485907200000"}},"sys":{"space":{"sys":{"type":"Link","linkType":"Space","id":"rue07lqzt1co"}},"type":"Entry","contentType":{"sys":{"type":"Link","linkType":"ContentType","id":"sFzTZbSuM8coEwygeUYes"}},"id":"4LgMotpNF6W20YKmuemW0a","revision":1,"createdAt":"2015-01-15T16:02:27.122Z","updatedAt":"2015-01-15T16:02:27.122Z"}}');

        $this->assertInstanceOf('\DateTimeImmutable', $entry->getSomeDate());
        $this->assertEquals('2017-02-01T00:00:00+00:00', $entry->getSomeDate()->format(\DateTime::ATOM));
    }
}

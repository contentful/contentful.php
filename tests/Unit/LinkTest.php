<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit;

use Contentful\Link;

class LinkTest extends \PHPUnit_Framework_TestCase
{
    public function testGetter()
    {
        $link = new Link('123', 'Entry');

        $this->assertEquals('123', $link->getId());
        $this->assertEquals('Entry', $link->getLinkType());
    }

    public function testJsonSerialize()
    {
        $link = new Link('123', 'Entry');

        $this->assertJsonStringEqualsJsonString('{"sys": {"type": "Link", "id": "123", "linkType": "Entry"}}', json_encode($link));
    }
}

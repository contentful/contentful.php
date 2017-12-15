<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit;

use Contentful\Location;

class LocationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $lat = 15.0;
        $long = 17.8;

        $loc = new Location($lat, $long);
        $this->assertSame($lat, $loc->getLatitude());
        $this->assertSame($long, $loc->getLongitude());
    }

    public function testJsonSerialization()
    {
        $loc = new Location(15.0, 17.8);

        $this->assertJsonStringEqualsJsonString('{"lat":15,"long":17.8}', \json_encode($loc));
    }

    public function testQueryStringFormatted()
    {
        $loc = new Location(15.0, 17.8);

        $this->assertSame('15,17.8', $loc->queryStringFormatted());
    }
}

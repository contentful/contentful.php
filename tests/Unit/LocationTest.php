<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit;

use Contentful\Location;

class LocationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Contentful\Location::__construct
     * @covers \Contentful\Location::getLatitude
     * @covers \Contentful\Location::getLongitude
     */
    public function testGetters()
    {
        $lat = 15.0;
        $long = 17.8;

        $loc = new Location($lat, $long);
        $this->assertEquals($lat, $loc->getLatitude());
        $this->assertEquals($long, $loc->getLongitude());
    }

    /**
     * @covers \Contentful\Location::__construct
     * @covers \Contentful\Location::jsonSerialize
     */
    public function testJsonSerialization()
    {
        $loc = new Location(15.0, 17.8);

        $this->assertJsonStringEqualsJsonString('{"lat":15,"long":17.8}', json_encode($loc));
    }

    /**
     * @covers \Contentful\Location::__construct
     * @covers \Contentful\Location::queryStringFormatted
     */
    public function testQueryStringFormatted()
    {
        $loc = new Location(15.0, 17.8);

        $this->assertEquals('15,17.8', $loc->queryStringFormatted());
    }
}

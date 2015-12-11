<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit;

use Contentful\ResourceArray;
use Contentful\Location;
use Contentful\Delivery\ContentType;

class ResourceArrayTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSet()
    {
        $arr = new ResourceArray(['abc'], 10, 2, 0);

        $this->assertEquals(10, $arr->getTotal());
        $this->assertEquals(2, $arr->getLimit());
        $this->assertEquals(0, $arr->getSkip());
    }

    public function testCountable()
    {
        $arr = new ResourceArray(['abc'], 10, 2, 0);

        $this->assertInstanceOf('\Countable', $arr);
        $this->assertCount(1, $arr);
    }

    public function testArrayAccess()
    {
        $arr = new ResourceArray(['abc'], 10, 2, 0);

        $this->assertInstanceOf('\Countable', $arr);
        $this->assertTrue(isset($arr[0]));
        $this->assertEquals('abc', $arr[0]);
    }

    public function testJsonSerializeEmpty()
    {
        $arr = new ResourceArray([], 0, 10, 0);

        $this->assertJsonStringEqualsJsonString('{"sys":{"type":"Array"},"total":0,"limit":10,"skip":0,"items":[]}', json_encode($arr));
    }

    public function testIsIterable()
    {
        $arr = new ResourceArray(['abc'], 10, 2, 0);

        $this->assertInstanceOf('\Traversable', $arr);
    }

    public function testIteration()
    {
        $arr = new ResourceArray(['abc', 'def'], 10, 2, 0);
        $count = 0;

        foreach ($arr as $key => $elem) {
            $count++;
            $this->assertEquals($arr[$key], $elem);
        }

        $this->assertEquals(2, $count);
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testOffsetSetThrows()
    {
        $arr = new ResourceArray([], 0, 2, 0);

        $arr[0] = 'abc';
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testOffsetUnsetThrows()
    {
        $arr = new ResourceArray(['abc'], 10, 2, 0);

        unset($arr[0]);
    }
}

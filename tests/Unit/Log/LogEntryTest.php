<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Log;

use Contentful\Log\LogEntry;
use GuzzleHttp\Psr7\Request;

class LogEntryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetter()
    {
        $request = new Request('GET', 'http://cdn.contentful.com/spaces/');
        $entry = new LogEntry('DELIVERY', $request, 5);

        $this->assertEquals('DELIVERY', $entry->getApi());
        $this->assertSame($request, $entry->getRequest());
        $this->assertEquals(5, $entry->getDuration());
        $this->assertNull($entry->getResponse());
        $this->assertNull($entry->getException());
        $this->assertFalse($entry->isError());
    }

    public function testSerialize()
    {
        $entry = new LogEntry('DELIVERY', new Request('GET', 'http://cdn.contentful.com/spaces/'), 0);

        $serialized = unserialize(serialize($entry));

        $this->assertEquals($entry->getApi(), $serialized->getApi());
        $this->assertEquals($entry->getRequest()->getMethod(), $serialized->getRequest()->getMethod());
        $this->assertEquals($entry->getRequest()->getUri(), $serialized->getRequest()->getUri());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorThrowsOnInvalidAPI()
    {
        new LogEntry('NOPE', new Request('GET', 'http://cdn.contentful.com/spaces/'), 0);
    }
}

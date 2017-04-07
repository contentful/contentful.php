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
    public function testSerialize()
    {
        $entry = new LogEntry('DELIVERY', new Request('GET', 'http://cdn.contentful.com/spaces/'), 0);

        $serialized = unserialize(serialize($entry));

        $this->assertEquals($entry->getApi(), $serialized->getApi());
        $this->assertEquals($entry->getRequest()->getMethod(), $serialized->getRequest()->getMethod());
        $this->assertEquals($entry->getRequest()->getUri(), $serialized->getRequest()->getUri());
    }
}

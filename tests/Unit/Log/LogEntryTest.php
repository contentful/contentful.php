<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2017 Contentful GmbH
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

        $this->assertSame('DELIVERY', $entry->getApi());
        $this->assertSame($request, $entry->getRequest());
        $this->assertSame(5, $entry->getDuration());
        $this->assertNull($entry->getResponse());
        $this->assertNull($entry->getException());
        $this->assertFalse($entry->isError());
    }

    public function testSerialize()
    {
        $entry = new LogEntry('DELIVERY', new Request('GET', 'http://cdn.contentful.com/spaces/'), 0);

        $serialized = \unserialize(\serialize($entry));

        $this->assertSame($entry->getApi(), $serialized->getApi());
        $this->assertSame($entry->getRequest()->getMethod(), $serialized->getRequest()->getMethod());
        $this->assertSame((string) $entry->getRequest()->getUri(), (string) $serialized->getRequest()->getUri());
    }

    public function testSerializeWithException()
    {
        $errorMessage = 'error messages';
        $errorCode = 999;
        $exception = null;

        $closure1 = function ($closure) {
            $closure();
        };

        $closure2 = function () use (&$exception, $errorMessage, $errorCode) {
            $exception = new \Exception($errorMessage, $errorCode);
        };

        $closure1($closure2);

        /** @var \Exception $exception $traceBeforeFix */
        $traceBeforeFix = $exception->getTrace();

        $entry = new LogEntry(
            'DELIVERY',
            new Request('GET', 'http://cdn.contentful.com/spaces/'),
            0,
            null,
            $exception
        );

        $serialized = \unserialize(\serialize($entry));

        $this->assertSame($entry->getApi(), $serialized->getApi());
        $this->assertSame($entry->getRequest()->getMethod(), $serialized->getRequest()->getMethod());
        $this->assertSame((string) $entry->getRequest()->getUri(), (string) $serialized->getRequest()->getUri());
        // Makes sure that in this case, the trace was modified
        $this->assertNotSame($traceBeforeFix, $serialized->getException()->getTrace());
        // A raw check for the presence of the normalized closure string
        $this->assertContains('(Closure in file', $serialized->getException()->getTrace()[1]['args'][0]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorThrowsOnInvalidAPI()
    {
        new LogEntry('NOPE', new Request('GET', 'http://cdn.contentful.com/spaces/'), 0);
    }
}

<?php
/*
 * @copyright 2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Client;
use Contentful\Log\ArrayLogger;

class GzipEncodingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @vcr e2e_gzip_encoding.json
     */
    public function testContentEncodingHeader()
    {
        $logger = new ArrayLogger;
        $client = new Client('b4c0n73n7fu1', 'cfexampleapi', false, $logger);

        $client->getEntries();

        $logEntry = $logger->getLogs()[0];

        $this->assertEquals('gzip', $logEntry->getRequest()->getHeaderLine('Accept-Encoding'));

        // Need to check 'x-encoded-content-encoding' as curl is automatically decompressing the response
        $this->assertEquals('gzip', $logEntry->getResponse()->getHeaderLine('x-encoded-content-encoding'));
    }
}

<?php
/*
 * @copyright 2016-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Query;
use Contentful\Tests\Delivery\End2EndTestCase;

class GzipEncodingTest extends End2EndTestCase
{
    /**
     * @requires API no-coverage-proxy
     * @vcr e2e_gzip_encoding.json
     */
    public function testContentEncodingHeader()
    {
        $client = $this->getClient('cfexampleapi_logger');
        $logger = $client->getLogger();

        $query = (new Query())
            ->setLocale('*');
        $client->getEntries($query);

        $logEntry = $logger->getLogs()[0];

        $this->assertEquals('gzip', $logEntry->getRequest()->getHeaderLine('Accept-Encoding'));

        // Need to check 'X-Encoded-Content-Encoding' as curl is automatically decompressing the response
        $this->assertEquals('gzip', $logEntry->getResponse()->getHeaderLine('X-Encoded-Content-Encoding'));
    }
}

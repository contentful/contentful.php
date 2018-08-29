<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery\E2E;

use Contentful\Delivery\Query;
use Contentful\Tests\Delivery\TestCase;

class GzipEncodingTest extends TestCase
{
    /**
     * @requires API no-coverage-proxy
     * @vcr e2e_gzip_encoding.json
     */
    public function testContentEncodingHeader()
    {
        $client = $this->getClient('cfexampleapi');

        $query = (new Query())
            ->setLocale('*')
        ;
        $client->getEntries($query);

        $message = $client->getMessages()[0];

        $this->assertSame('gzip', $message->getRequest()->getHeaderLine('Accept-Encoding'));

        // Need to check 'X-Encoded-Content-Encoding' as curl is automatically decompressing the response
        $this->assertSame('gzip', $message->getResponse()->getHeaderLine('X-Encoded-Content-Encoding'));
    }
}

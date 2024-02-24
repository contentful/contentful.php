<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\E2E;

use Contentful\Delivery\Query;
use Contentful\Tests\Delivery\TestCase;

class GzipEncodingTest extends TestCase
{
    /**
     * @vcr gzip_encoding_content_encoding_header.json
     */
    public function testContentEncodingHeader()
    {
        $this->skipIfApiCoverage();

        $client = $this->getClient('default');

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

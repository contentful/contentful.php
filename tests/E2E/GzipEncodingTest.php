<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Query;
use Contentful\Tests\DeliveryEnd2EndTestCase;

class GzipEncodingTest extends DeliveryEnd2EndTestCase
{
    /**
     * @requires API no-coverage-proxy
     * @vcr e2e_gzip_encoding.json
     */
    public function testContentEncodingHeader()
    {
        $client = $this->getClient('cfexampleapi');

        $query = (new Query())
            ->setLocale('*');
        $client->getEntries($query);

        $messages = $client->getMessages();
        $this->assertSame('gzip', $messages[0]->getRequest()->getHeaderLine('Accept-Encoding'));

        // Need to check 'X-Encoded-Content-Encoding' as curl is automatically decompressing the response
        $this->assertSame('gzip', $messages[0]->getResponse()->getHeaderLine('X-Encoded-Content-Encoding'));
    }
}

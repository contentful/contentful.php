<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit;

use Contentful\Exception\AccessTokenInvalidException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class ApiExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionNoResponse()
    {
        $request = new Request('GET', 'https://cdn.contentful.com/spaces/cfexampleapi/entries');

        $guzzleException = new ClientException('This is an error', $request);
        $exception = new AccessTokenInvalidException($guzzleException);

        $this->assertSame('This is an error', $exception->getMessage());
    }

    public function testExceptionNoMessage()
    {
        $request = new Request('GET', 'https://cdn.contentful.com/spaces/cfexampleapi/entries');
        $response = new Response(
            401,
            ['X-Contentful-Request-Id' => '426753a1639d40c23ad4cbf085a072c7'],
            '{"sys": {"type": "Error","id": "AccessTokenInvalid","requestId":"426753a1639d40c23ad4cbf085a072c7"}',
            1.1,
            'Unauthorized'
        );

        $guzzleException = new ClientException('This is an error', $request, $response);
        $exception = new AccessTokenInvalidException($guzzleException);

        $this->assertSame('This is an error', $exception->getMessage());
    }

    public function testExceptionMalformedJsonResponse()
    {
        $request = new Request('GET', 'https://cdn.contentful.com/spaces/cfexampleapi/entries');
        $response = new Response(
            401,
            ['X-Contentful-Request-Id' => '426753a1639d40c23ad4cbf085a072c7'],
            '{"sys": {"type": "Error","id": "AccessTokenInvalid","message": "The access token you sent could not be found or is invalid.","requestId":"426753a1639d40c23ad4cbf085a072c7"}',
            1.1,
            'Unauthorized'
        );

        $guzzleException = new ClientException('This is an error', $request, $response);
        $exception = new AccessTokenInvalidException($guzzleException);

        $this->assertSame('This is an error', $exception->getMessage());
    }
}

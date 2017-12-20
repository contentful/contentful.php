<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit;

use Contentful\Exception\InvalidQueryException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class InvalidQueryExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionStructure()
    {
        $request = new Request('GET', 'https://cdn.contentful.com/spaces/cfexampleapi/entries?name=0');
        $response = new Response(
            400,
            ['X-Contentful-Request-Id' => '18e21420a62b690effa8f80c8b8766b0'],
            '{"sys": {"type": "Error","id": "InvalidQuery"},"message": "The query you sent was invalid. Probably a filter or ordering specification is not applicable to the type of a field.","details": {"errors": [{"name": "unknown","path": ["name"],"details": "The path \"name\" is not recognized"}]},"requestId":"18e21420a62b690effa8f80c8b8766b0"}',
            1.1,
            'Bad Request'
        );

        $guzzleException = new ClientException('This is an error', $request, $response);

        $exception = new InvalidQueryException($guzzleException);

        $this->assertTrue($exception->hasResponse());
        $this->assertSame($request, $exception->getRequest());
        $this->assertSame($response, $exception->getResponse());
        $this->assertSame('18e21420a62b690effa8f80c8b8766b0', $exception->getRequestId());
        $this->assertSame('The query you sent was invalid. Probably a filter or ordering specification is not applicable to the type of a field.', $exception->getMessage());
    }
}

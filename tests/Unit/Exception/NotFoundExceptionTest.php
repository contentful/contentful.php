<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit;

use Contentful\Exception\NotFoundException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class NotFoundExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionStructure()
    {
        $request = new Request('GET', 'https://cdn.contentful.com/spaces/cfexampleapi/entries/not-existing');
        $response = new Response(
            404,
            ['X-Contentful-Request-Id' => 'db2d795acb78e0592af00759986c744b'],
            '{"sys": {"type": "Error","id": "NotFound"},"message": "The resource could not be found.","details": {"type": "Entry","id": "not-existing","space": "cfexampleapi"},"requestId": "db2d795acb78e0592af00759986c744b"}',
            1.1,
            'Not Found'
        );

        $guzzleException = new ClientException('This is an error', $request, $response);

        $exception = new NotFoundException($guzzleException);

        $this->assertTrue($exception->hasResponse());
        $this->assertSame($request, $exception->getRequest());
        $this->assertSame($response, $exception->getResponse());
        $this->assertEquals('db2d795acb78e0592af00759986c744b', $exception->getRequestId());
        $this->assertEquals('The resource could not be found.', $exception->getMessage());
    }
}

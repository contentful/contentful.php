<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful;

use GuzzleHttp\Psr7\Response;

/**
 * ResponseInfo class.
 *
 * This class receives a Response object generated from a request to Contentful,
 * and it stores useful data that is described in the response headers.
 */
abstract class ResponseInfo
{
    /**
     * @var string
     */
    private $requestId;

    /**
     * Constructor.
     *
     * @param Response $response
     */
    public function __construct(Response $response)
    {
        $this->requestId = $response->getHeaderLine('X-Contentful-Request-Id');
    }

    /**
     * @return string
     */
    public function getRequestId()
    {
        return $this->requestId;
    }
}

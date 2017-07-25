<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Exception;

use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * A RequestException is thrown when an errors occurs while communicating with the API.
 *
 * @api
 */
abstract class ApiException extends \RuntimeException
{
    /**
     * @var GuzzleRequestException
     */
    private $previous;

    /**
     * @var string|null
     */
    private $requestId = null;

    /**
     * @var \Psr\Http\Message\RequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface|null
     */
    private $response;

    /**
     * RequestException constructor.
     *
     * @param GuzzleRequestException $previous
     * @param string                 $message
     */
    public function __construct(GuzzleRequestException $previous, $message = '')
    {
        $this->previous = $previous;
        $this->request = $previous->getRequest();

        if ($this->previous->hasResponse()) {
            $this->response = $this->previous->getResponse();
            $this->requestId = $this->response->getHeader('X-Contentful-Request-Id')[0];
        }
        if ($message === '') {
            $message = self::createExceptionMessage($previous, $this->response);
        }

        parent::__construct($message, 0, $previous);
    }

    private static function createExceptionMessage(GuzzleRequestException $previous, ResponseInterface $response = null)
    {
        if (!$response) {
            return $previous->getMessage();
        }

        try {
            $result = \GuzzleHttp\json_decode($response->getBody(), true);
            if (isset($result['message'])) {
                return $result['message'];
            }
        } catch (\InvalidArgumentException $e) {
            return $previous->getMessage();
        }

        return $previous->getMessage();
    }

    /**
     * Get the request that caused the exception
     *
     * @return \Psr\Http\Message\RequestInterface
     *
     * @api
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the associated response
     *
     * @return ResponseInterface|null
     *
     * @api
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Check if a response was received
     *
     * @return bool
     *
     * @api
     */
    public function hasResponse()
    {
        return $this->response !== null;
    }

    /**
     * @return string|null
     *
     * @api
     */
    public function getRequestId()
    {
        return $this->requestId;
    }
}

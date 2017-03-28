<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Log;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class LogEntry implements \Serializable
{
    /**
     * @var string
     */
    private $api;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var float
     */
    private $duration;

    /**
     * @var ResponseInterface|null
     */
    private $response;

    /**
     * @var \Exception|null
     */
    private $exception;

    /**
     * LogEntry constructor.
     *
     * @param string                 $api
     * @param RequestInterface       $request
     * @param float|null             $duration
     * @param ResponseInterface|null $response
     * @param \Exception|null        $exception
     *
     * @throws \InvalidArgumentException When $api is an unrecognized value
     */
    public function __construct($api, RequestInterface $request, $duration, ResponseInterface $response = null, \Exception $exception = null)
    {
        if (!in_array($api, ['DELIVERY', 'PREVIEW', 'MANAGEMENT'], true)) {
            throw new \InvalidArgumentException('Unknown API type "' . $api . '"');
        }

        $this->api = $api;
        $this->request = $request;
        $this->exception = $exception;
        $this->duration = $duration;
        $this->response = $response;
    }

    /**
     * @return string
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return \Exception|null
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @return float
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @return ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * True if the requests threw an error.
     *
     * @return bool
     */
    public function isError()
    {
        return $this->exception !== null;
    }

    public function serialize()
    {
        return serialize((object) [
            'api' => $this->api,
            'duration' => $this->duration,
            'exception' => $this->exception,
            'request' => \GuzzleHttp\Psr7\str($this->request),
            'response' => \GuzzleHttp\Psr7\str($this->response)
        ]);
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        $this->api = $data->api;
        $this->duration = $data->duration;
        $this->exception = $data->exception;
        $this->request = \GuzzleHttp\Psr7\parse_request($data->request);
        $this->response = \GuzzleHttp\Psr7\parse_response($data->response);
    }
}

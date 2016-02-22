<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Log;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class LogEntry
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
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var \Exception
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
}

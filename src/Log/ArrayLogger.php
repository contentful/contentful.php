<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Log;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Saves requests information in array so they can be retrieved later.
 */
class ArrayLogger implements LoggerInterface
{
    /**
     * @var array
     */
    private $logs = [];

    /**
     * Empty constructor for forward compatibility.
     */
    public function __construct()
    {
    }

    /**
     * Returns a timer to be used to gather timing information for the next request.
     *
     * @return StandardTimer
     */
    public function getTimer()
    {
        return new StandardTimer;
    }

    /**
     * Returns the collected logs.
     *
     * @return LogEntry[]
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * Log information about a request.
     *
     * @param string                 $api
     * @param RequestInterface       $request
     * @param TimerInterface         $timer
     * @param ResponseInterface|null $response
     * @param \Exception|null        $exception
     *
     * @return void
     *
     * @throws \InvalidArgumentException When $api is an unrecognized value
     */
    public function log($api, RequestInterface $request, TimerInterface $timer, ResponseInterface $response = null, \Exception $exception = null)
    {
        $this->logs[] = new LogEntry($api, $request, $timer->getDuration(), $response, $exception);
    }
}

<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Log;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Implementation of LoggerInterface that logs nothing.
 *
 * Used in production to reduce overhead.
 */
class NullLogger implements LoggerInterface
{
    /**
     * Empty constructor for forward compatibility.
     */
    public function __construct()
    {
    }

    /**
     * @return NullTimer
     */
    public function getTimer()
    {
        return new NullTimer;
    }

    /**
     * Log information about a request.
     *
     * @param  string                 $api
     * @param  RequestInterface       $request
     * @param  TimerInterface         $timer
     * @param  ResponseInterface|null $response
     * @param  \Exception|null        $exception
     *
     * @return void
     */
    public function log($api, RequestInterface $request, TimerInterface $timer, ResponseInterface $response = null, \Exception $exception = null)
    {
    }
}

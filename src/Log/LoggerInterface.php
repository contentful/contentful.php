<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Log;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface LoggerInterface
{
    /**
     * Returns a timer to be used to gather timing information for the next request.
     *
     * @return TimerInterface
     */
    public function getTimer();

    /**
     * Log information about a request.
     *
     * @param float                  $api
     * @param RequestInterface       $request
     * @param TimerInterface         $timer
     * @param ResponseInterface|null $response
     * @param \Exception|null        $exception
     *
     * @return void
     */
    public function log($api, RequestInterface $request, TimerInterface $timer, ResponseInterface $response = null, \Exception $exception = null);
}

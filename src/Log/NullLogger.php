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

    public function log($api, RequestInterface $request, StandardTimer $timer, ResponseInterface $response = null, \Exception $exception = null)
    {
    }
}

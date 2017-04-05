<?php
/**
 * @copyright 2016-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Exception;

/**
 * A RateLimitExceededException is thrown when there have been too many requests.
 *
 * The usual RateLimit on the Content Delivery API is 216000 requests/hour and 78 requests/second.
 * Responses cached by the Contentful CDN don't count against the rate limit.
 *
 * @api
 */
class RateLimitExceededException extends \RuntimeException
{
    /**
     * @var int|null
     */
    private $rateLimitReset;

    /**
     * RateLimitExceededException constructor.
     *
     * @param string          $message
     * @param int             $code
     * @param \Exception|null  $previous
     * @param int|null        $rateLimitReset
     */
    public function __construct($message = "", $code = 0, \Exception $previous = null, $rateLimitReset = null)
    {
        parent::__construct($message, $code, $previous);

        $this->rateLimitReset = $rateLimitReset;
    }

    /**
     * Returns the number of seconds until the rate limit expires.
     *
     * @return int|null
     */
    public function getRateLimitReset()
    {
        return $this->rateLimitReset;
    }
}

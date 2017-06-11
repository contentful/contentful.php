<?php
/**
 * @copyright 2016-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Exception;

use GuzzleHttp\Exception\RequestException as GuzzleRequestException;

/**
 * A RateLimitExceededException is thrown when there have been too many requests.
 *
 * The usual RateLimit on the Content Delivery API is 216000 requests/hour and 78 requests/second.
 * Responses cached by the Contentful CDN don't count against the rate limit.
 *
 * @api
 */
class RateLimitExceededException extends ApiException
{
    /**
     * @var int|null
     */
    private $rateLimitReset;

    /**
     * RateLimitExceededException constructor.
     *
     * @param GuzzleRequestException $previous
     * @param string                 $message
     */
    public function __construct(GuzzleRequestException $previous, $message = '')
    {
        parent::__construct($previous, $message);

        $this->rateLimitReset = (int) $this->getResponse()->getHeader('X-Contentful-RateLimit-Reset')[0];
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

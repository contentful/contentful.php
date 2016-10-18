<?php
/**
 * @copyright 2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful;

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
}

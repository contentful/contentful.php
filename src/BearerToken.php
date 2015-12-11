<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful;

use Psr\Http\Message\RequestInterface;

/**
 * BearerToken is a Guzzle handler to add the necessary headers to authenticate against the Contentful APIs.
 */
class BearerToken
{
    /**
     * Access token for the API
     *
     * @var string
     */
    private $token;

    /**
     * BearerToken constructor.
     *
     * @param string $token Access token for the API
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Makes this class callable. Runs the necessary set up to add the Authorization header.
     *
     * @param callable $handler
     *
     * @return \Closure
     */
    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $request = $this->onBefore($request);
            return $handler($request, $options);
        };
    }

    /**
     * Will be called by Guzzle. Adds the desired Authorization header.
     *
     * @param RequestInterface $request
     *
     * @return \Psr\Http\Message\MessageInterface
     */
    private function onBefore(RequestInterface $request)
    {
        return $request->withHeader('Authorization', 'Bearer ' . $this->token);
    }
}

<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Exception\ClientException;

/**
 * Abstract client for common code for the different clients.
 */
abstract class Client
{
    /**
     * @var GuzzleClient
     */
    private $httpClient;

    /**
     * Client constructor.
     *
     * @param string $token
     * @param string $baseUri
     * @param array  $headers
     */
    public function __construct($token, $baseUri, $headers = [])
    {
        $stack = HandlerStack::create();
        $stack->push(new BearerToken($token));
        $headers['User-Agent'] = $this->getUserAgent();

        $this->httpClient = new GuzzleClient([
            'base_uri' => $baseUri,
            'handler' => $stack,
            'headers' => $headers
        ]);
    }

    /**
     * @param string $method
     * @param string $path
     * @param array  $options
     *
     * @return array|object
     */
    protected function request($method, $path, array $options = [])
    {
        try {
            $response = $this->httpClient->request($method, $path, $options);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                throw new ResourceNotFoundException(null, 0, $e);
            }

            throw $e;
        }

        return $this->decodeJson($response->getBody());
    }

    abstract protected function getUserAgentAppName();

    protected function getUserAgent()
    {
        $agent = $this->getUserAgentAppName() . ' GuzzleHttp/' . GuzzleClient::VERSION;
        if (extension_loaded('curl') && function_exists('curl_version')) {
            $agent .= ' curl/' . \curl_version()['version'];
        }
        $agent .= ' PHP/' . \PHP_VERSION;

        return $agent;
    }

    /**
     * @param  string $json JSON encoded object or array
     *
     * @return object|array
     *
     * @throws \RuntimeException On invalid JSON
     */
    protected function decodeJson($json)
    {
        $result = json_decode($json);
        if ($result === null) {
            throw new \RuntimeException(json_last_error_msg(), json_last_error());
        }

        return $result;
    }
}

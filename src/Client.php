<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful;

use Contentful\Log\NullLogger;
use Contentful\Log\StandardTimer;
use Contentful\Exception\ResourceNotFoundException;
use Contentful\Exception\RateLimitExceededException;
use Contentful\Exception\InvalidQueryException;
use Contentful\Exception\AccessTokenInvalidException;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use Contentful\Log\LoggerInterface;
use GuzzleHttp\Psr7;

/**
 * Abstract client for common code for the different clients.
 */
abstract class Client
{
    /**
     * @var GuzzleClientInterface
     */
    private $httpClient;

    /**
     * @var string
     */
    private $baseUri;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $api;
    
    /**
     * @var string
     */
    private $token;

    /**
     * Client constructor.
     *
     * @param string                $token
     * @param string                $baseUri
     * @param string                $api
     * @param LoggerInterface       $logger
     * @param GuzzleClientInterface $guzzle
     */
    public function __construct($token, $baseUri, $api, LoggerInterface $logger = null, GuzzleClientInterface $guzzle = null)
    {
        $this->token = $token;
        $this->logger = $logger ?: new NullLogger();

        $this->api = $api;
        $this->baseUri = $baseUri;
        $this->httpClient = $guzzle ?: new GuzzleClient();
    }

    /**
     * @param string $method
     * @param string $path
     * @param array  $options
     *
     * @return array
     */
    protected function request($method, $path, array $options = [])
    {
        $timer = new StandardTimer;
        $timer->start();

        $query = isset($options['query']) ? $options['query'] : null;
        if ($query) {
            unset($options['query']);
        }
        $request = $this->buildRequest($method, $path, $query);

        // We define this variable so it's also available in the catch block.
        $response = null;
        try {
            $response = $this->doRequest($request, $options);
            $result = $this->decodeJson($response->getBody());
        } catch (\Exception $e) {
            $timer->stop();
            $this->logger->log($this->api, $request, $timer, $response, $e);

            throw $e;
        }

        $timer->stop();
        $this->logger->log($this->api, $request, $timer, $response);

        return $result;
    }

    private function doRequest($request, $options)
    {
        try {
            return $this->httpClient->send($request, $options);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            if ($response->getStatusCode() === 404) {
                $result = $this->decodeJson($response->getBody());
                throw new ResourceNotFoundException($result['message'], 0, $e);
            }
            if ($response->getStatusCode() === 429) {
                throw new RateLimitExceededException(null, 0, $e);
            }
            if ($response->getStatusCode() === 400) {
                $result = $this->decodeJson($response->getBody());
                if ($result['sys']['id'] === 'InvalidQuery') {
                    throw new InvalidQueryException($result['message'], 0, $e);
                }
            }
            if ($response->getStatusCode() === 401) {
                $result = $this->decodeJson($response->getBody());
                if ($result['sys']['id'] === 'AccessTokenInvalid') {
                    throw new AccessTokenInvalidException($result['message'], 0, $e);
                }
            }

            throw $e;
        }
    }

    /**
     * @param  string            $method
     * @param  string            $path
     * @param  array|string|null $query
     *
     * @return Psr7\Request
     *
     * @throws \InvalidArgumentException If $query is not a valid type
     */
    private function buildRequest($method, $path, $query = null)
    {
        $contentTypes = [
            'DELIVERY' => 'application/vnd.contentful.delivery.v1+json',
            'PREVIEW' => 'application/vnd.contentful.delivery.v1+json',
            'MANAGEMENT' => 'application/vnd.contentful.management.v1+json'
        ];

        $uri = Psr7\Uri::resolve(Psr7\uri_for($this->baseUri), $path);

        if ($query) {
            if (is_array($query)) {
                $query = http_build_query($query, null, '&', PHP_QUERY_RFC3986);
            }
            if (!is_string($query)) {
                throw new \InvalidArgumentException('query must be a string or array');
            }
            $uri = $uri->withQuery($query);
        }

        return new Psr7\Request($method, $uri, [
            'User-Agent' => $this->getUserAgent(),
            'Content-Type' => $contentTypes[$this->api],
            'Accept-Encoding' => 'gzip',
            'Authorization' => 'Bearer ' . $this->token,
        ], null);
    }

    /**
     * The name of the library to be used in the User-Agent header.
     *
     * @return string
     */
    abstract protected function getUserAgentAppName();

    /**
     * Returns the value of the User-Agent header for any requests made to Contentful
     *
     * @return string
     */
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
     * @return array
     *
     * @throws \RuntimeException On invalid JSON
     */
    protected function decodeJson($json)
    {
        $result = json_decode($json, true);
        if ($result === null) {
            throw new \RuntimeException(json_last_error_msg(), json_last_error());
        }

        return $result;
    }
}

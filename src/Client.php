<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful;

use Contentful\Log\NullLogger;
use Contentful\Log\StandardTimer;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Exception\ClientException;
use Contentful\Log\LoggerInterface;
use GuzzleHttp\Psr7;

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
     * Client constructor.
     *
     * @param string          $token
     * @param string          $baseUri
     * @param LoggerInterface $logger
     */
    public function __construct($token, $baseUri, $api, LoggerInterface $logger = null)
    {
        $stack = HandlerStack::create();
        $stack->push(new BearerToken($token));
        $this->logger = $logger ?: new NullLogger();

        $this->api = $api;
        $this->baseUri = $baseUri;
        $this->httpClient = new GuzzleClient([
            'handler' => $stack
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
        $timer = new StandardTimer;
        $timer->start();

        $query = isset($options['query']) ? $options['query'] : null;
        if ($query) {
            unset($options['query']);
        }
        $request = $this->buildRequest($method, $path, $query);

        try {
            $response = $this->doRequest($request, $options);
            $result = $this->decodeJson($response->getBody());
        }
        catch (\Exception $e) {
            $timer->stop();
            $this->logger->log('DELIVERY', $request, $timer, $e);

            throw $e;
        }

        $timer->stop();
        $this->logger->log('DELIVERY', $request, $timer, null);

        return $result;
    }

    private function doRequest($request, $options)
    {
        try {
            return $this->httpClient->send($request, $options);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                throw new ResourceNotFoundException(null, 0, $e);
            }

            throw $e;
        }
    }

    private function buildRequest($method, $path, $query = null)
    {
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

        return new Psr7\Request($method, $uri, ['User-Agent' => $this->getUserAgent()], null);
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

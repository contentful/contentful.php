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
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheAdapterInterface;

/**
 * Abstract client for common code for the different clients.
 */
abstract class Client
{
    /**
     * @var GuzzleClient
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $baseUri;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $api;

    /**
     * @var CacheAdapterInterface
     */
    protected $cache;

    /**
     * Client constructor.
     *
     * @param string          $token
     * @param string          $baseUri
     * @param string          $api
     * @param CacheAdapterInterface $cache
     * @param LoggerInterface $logger
     */
    public function __construct($token, $baseUri, $api, CacheAdapterInterface $cache, LoggerInterface $logger = null)
    {
        $stack = HandlerStack::create();
        $stack->push(new BearerToken($token));
        $this->logger = $logger ?: new NullLogger();

        $this->cache = $cache;

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
            $cacheKey = preg_replace('#[{}()\/\\@:]+#', '', $request->getUri());

            $cacheItem = $this->cache->getItem($cacheKey);
            if (! $cacheItem->isHit()) {
                $cacheItem->set($this->getContents($request, $options));
                $this->cache->save($cacheItem);
            }

            $result = $this->decodeJson($cacheItem->get());
        }
        catch (\Exception $e) {
            $timer->stop();

            throw $e;
        }

        $timer->stop();

        return $result;
    }

    protected function getContents($request, $options)
    {
        $response = $this->doRequest($request, $options);

        return $response->getBody()->getContents();
    }

    protected function doRequest($request, $options)
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

    /**
     * @param  string            $method
     * @param  string            $path
     * @param  array|string|null $query
     *
     * @return Psr7\Request
     *
     * @throws \InvalidArgumentException If $query is not a valid type
     */
    protected function buildRequest($method, $path, $query = null)
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
            'Accept-Encoding' => 'gzip'
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

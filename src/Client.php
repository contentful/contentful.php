<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful;

use Contentful\Log\LoggerInterface;
use Contentful\Log\NullLogger;
use Contentful\Log\StandardTimer;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7;
use Psr\Http\Message\RequestInterface;

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
     * @var Psr7\Uri
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
     * @var string
     */
    private $applicationName;

    /**
     * @var string
     */
    private $applicationVersion;

    /**
     * @var string
     */
    private $integrationName;

    /**
     * @var string
     */
    private $integrationVersion;

    /*
     * @var string|null
     */
    private $contentfulUserAgent;

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
        $this->baseUri = new Psr7\Uri($baseUri);
        $this->httpClient = $guzzle ?: new GuzzleClient();
        $this->contentfulUserAgent = $this->getContentfulUserAgent();
    }

    /**
     * Set the application name and version. The values are used as part of the X-Contentful-User-Agent header.
     *
     * @param string|null $name
     * @param string|null $version
     *
     * @return $this
     */
    public function setApplication($name, $version = null)
    {
        $this->applicationName = $name;
        $this->applicationVersion = $version;

        // Update the cached value
        $this->contentfulUserAgent = $this->getContentfulUserAgent();

        return $this;
    }

    /**
     * Set the application name and version. The values are used as part of the X-Contentful-User-Agent header.
     *
     * @param string|null $name
     * @param string|null $version
     *
     * @return $this
     */
    public function setIntegration($name, $version = null)
    {
        $this->integrationName = $name;
        $this->integrationVersion = $version;

        // Update the cached value
        $this->contentfulUserAgent = $this->getContentfulUserAgent();

        return $this;
    }

    /**
     * @param string $method
     * @param string $path
     * @param array  $options
     *
     * @return array|null
     */
    protected function request($method, $path, array $options = [])
    {
        $timer = new StandardTimer();
        $timer->start();

        $query = isset($options['query']) ? $options['query'] : null;
        if ($query) {
            unset($options['query']);
        }

        $additionalHeaders = isset($options['additionalHeaders']) ? $options['additionalHeaders'] : [];
        if (!empty($additionalHeaders)) {
            unset($options['additionalHeaders']);
        }

        $body = isset($options['body']) ? $options['body'] : null;
        if ($body) {
            unset($options['body']);
        }

        $request = $this->buildRequest($method, $path, $query, $additionalHeaders, $body);

        // We define this variable so it's also available in the catch block.
        $response = null;
        try {
            $response = $this->doRequest($request, $options);
            if ($response->getStatusCode() === 204) {
                $result = null;
            } else {
                $result = self::decodeJson($response->getBody());
            }
        } catch (\Exception $e) {
            $timer->stop();
            $this->logger->log($this->api, $request, $timer, $response, $e);

            throw $e;
        }

        $timer->stop();
        $this->logger->log($this->api, $request, $timer, $response);

        return $result;
    }

    /**
     * @param RequestInterface $request
     * @param array            $options
     *
     * @return \Psr\Http\Message\ResponseInterface|null
     */
    private function doRequest(RequestInterface $request, array $options)
    {
        try {
            return $this->httpClient->send($request, $options);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            if ($response->getStatusCode() === 404) {
                $result = self::decodeJson($response->getBody());
                throw new Exception\NotFoundException($result['message'], 0, $e);
            }
            if ($response->getStatusCode() === 429) {
                $result = self::decodeJson($response->getBody());
                $rateLimitReset = (int) $response->getHeader('X-Contentful-RateLimit-Reset')[0];
                throw new Exception\RateLimitExceededException($result['message'], 0, $e, $rateLimitReset);
            }
            if ($response->getStatusCode() === 400) {
                $result = self::decodeJson($response->getBody());
                if ($result['sys']['id'] === 'InvalidQuery') {
                    throw new Exception\InvalidQueryException($result['message'], 0, $e);
                }
                if ($result['sys']['id'] === 'UnknownKey') {
                    throw new Exception\UnknownKeyException($result['message'], 0, $e);
                }
            }
            if ($response->getStatusCode() === 403) {
                $result = self::decodeJson($response->getBody());
                if ($result['sys']['id'] === 'Forbidden') {
                    throw new Exception\ForbiddenException($result['message'], 0, $e);
                }
            }
            if ($response->getStatusCode() === 401) {
                $result = self::decodeJson($response->getBody());
                if ($result['sys']['id'] === 'AccessTokenInvalid') {
                    throw new Exception\AccessTokenInvalidException($result['message'], 0, $e);
                }
            }
            if ($response->getStatusCode() === 422) {
                $result = self::decodeJson($response->getBody());
                if ($result['sys']['id'] === 'ValidationFailed') {
                    throw new Exception\ValidationFailedException($result['message'], 0, $e);
                }
            }

            throw $e;
        }
    }

    /**
     * @param string            $method
     * @param string            $path
     * @param array|string|null $query
     * @param array             $additionalHeaders
     * @param string            $body
     *
     * @throws \InvalidArgumentException If $query is not a valid type
     *
     * @return Psr7\Request
     */
    private function buildRequest($method, $path, $query = null, array $additionalHeaders = [], $body = null)
    {
        $contentTypes = [
            'DELIVERY'   => 'application/vnd.contentful.delivery.v1+json',
            'PREVIEW'    => 'application/vnd.contentful.delivery.v1+json',
            'MANAGEMENT' => 'application/vnd.contentful.management.v1+json',
        ];

        $uri = Psr7\UriResolver::resolve($this->baseUri, new Psr7\Uri($path));

        if ($query) {
            if (is_array($query)) {
                $query = http_build_query($query, null, '&', PHP_QUERY_RFC3986);
            }
            if (!is_string($query)) {
                throw new \InvalidArgumentException('query must be a string or array');
            }
            $uri = $uri->withQuery($query);
        }
        $headers = [
            'X-Contentful-User-Agent' => $this->contentfulUserAgent,
            'Accept'                  => $contentTypes[$this->api],
            'Accept-Encoding'         => 'gzip',
            'Authorization'           => 'Bearer '.$this->token,
        ];
        if ($body) {
            $headers['Content-Type'] = $contentTypes[$this->api];
        }

        $headers = array_merge($headers, $additionalHeaders);

        return new Psr7\Request($method, $uri, $headers, $body);
    }

    /**
     * The name of the library to be used in the User-Agent header.
     *
     * @return string
     */
    abstract protected function getSdkNameAndVersion();

    /**
     * Returns the value of the User-Agent header for any requests made to Contentful.
     *
     * @return string
     */
    protected function getContentfulUserAgent()
    {
        $possibleOperatingSystems = [
            'WINNT'  => 'Windows',
            'Darwin' => 'macOS',
        ];

        $parts = [
            'app'         => '',
            'integration' => '',
            'sdk'         => $this->getSdkNameAndVersion(),
            'platform'    => 'PHP/'.\PHP_VERSION,
            'os'          => isset($possibleOperatingSystems[PHP_OS]) ? $possibleOperatingSystems[PHP_OS] : 'Linux',
        ];

        if ($this->applicationName !== null) {
            $parts['app'] = $this->applicationName;
            if ($this->applicationVersion !== null) {
                $parts['app'] .= '/'.$this->applicationVersion;
            }
        }

        if ($this->integrationName !== null) {
            $parts['integration'] = $this->integrationName;
            if ($this->integrationVersion !== null) {
                $parts['integration'] .= '/'.$this->integrationVersion;
            }
        }

        $agent = '';
        foreach ($parts as $key => $value) {
            if ($value === '') {
                continue;
            }
            $agent .= $key.' '.$value.'; ';
        }

        return trim($agent);
    }

    /**
     * @param string $json JSON encoded object or array
     *
     * @throws \RuntimeException On invalid JSON
     *
     * @return array
     *
     * @internal
     */
    public static function decodeJson($json)
    {
        $result = json_decode($json, true);
        if ($result === null) {
            throw new \RuntimeException(json_last_error_msg(), json_last_error());
        }

        return $result;
    }

    protected function encodeJson($object)
    {
        $result = \GuzzleHttp\json_encode($object, JSON_UNESCAPED_UNICODE);
        if ($result === null) {
            throw new \RuntimeException(json_last_error_msg(), json_last_error());
        }

        return $result;
    }
}

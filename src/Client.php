<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful;

use Contentful\Log\NullLogger;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use Contentful\Log\LoggerInterface;
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
     * @var UserAgentGenerator
     */
    private $userAgentGenerator;

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
        $this->userAgentGenerator = new UserAgentGenerator($this->getSdkName(), $this->getSdkVersion());
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
        $this->userAgentGenerator->setApplication($name, $version);

        return $this;
    }

    /**
     * Set the integration name and version. The values are used as part of the X-Contentful-User-Agent header.
     *
     * @param string|null $name
     * @param string|null $version
     *
     * @return $this
     */
    public function setIntegration($name, $version = null)
    {
        $this->userAgentGenerator->setIntegration($name, $version);

        return $this;
    }

    /**
     * @param string $method  The HTTP method
     * @param string $path    The URI path
     * @param array  $options An array of optional parameters. The following keys are accepted:
     *                         * query             An array of query parameters that will be appended to the URI
     *                         * additionalHeaders An array of headers that will be added to the request
     *                         * body              The request body
     *                         * baseUri           A string that can be used to override the default client base URI
     *
     * @return array|null
     */
    protected function request($method, $path, array $options = [])
    {
        $timer = $this->logger->getTimer();
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

        $baseUri = isset($options['baseUri']) ? $options['baseUri'] : null;
        if ($baseUri) {
            unset($options['baseUri']);
        }

        $request = $this->buildRequest($method, $path, $query, $additionalHeaders, $body, $baseUri);

        // We define this variable so it's also available in the catch block.
        $response = null;
        try {
            $response = $this->doRequest($request, $options);
            if ($response->getStatusCode() === 204) {
                $result = null;
            } else {
                $result = \GuzzleHttp\json_decode($response->getBody(), true);
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
     * @param  RequestInterface $request
     * @param  array            $options
     *
     * @return \Psr\Http\Message\ResponseInterface|null
     */
    private function doRequest(RequestInterface $request, array $options)
    {
        try {
            return $this->httpClient->send($request, $options);
        } catch (ClientException $e) {
            if (!$e->hasResponse()) {
                throw $e;
            }

            $data = \GuzzleHttp\json_decode($e->getResponse()->getBody(), true);
            $errorId = $data['sys']['id'];
            $exceptionMap = $this->getExceptionMap();

            if (!isset($exceptionMap[$errorId])) {
                throw $e;
            }

            throw new $exceptionMap[$errorId]($e);
        }
    }

    /**
     * @param string     $method
     * @param string     $path
     * @param array|null $query
     * @param array      $additionalHeaders
     * @param string     $body
     * @param string     $baseUri
     *
     * @return Psr7\Request
     *
     * @throws \InvalidArgumentException If $query is not a valid type
     */
    private function buildRequest($method, $path, array $query = null, array $additionalHeaders = [], $body = null, $baseUri = null)
    {
        $baseUri = $baseUri ? new Psr7\Uri($baseUri) : $this->baseUri;

        $uri = Psr7\UriResolver::resolve($baseUri, new Psr7\Uri($path));

        if ($query) {
            $serializedQuery = http_build_query($query, null, '&', PHP_QUERY_RFC3986);
            $uri = $uri->withQuery($serializedQuery);
        }
        $headers = [
            'X-Contentful-User-Agent' => $this->userAgentGenerator->getUserAgent(),
            'Accept' => $this->getApiContentType(),
            'Accept-Encoding' => 'gzip',
            'Authorization' => 'Bearer ' . $this->token,
        ];
        if ($body) {
            $headers['Content-Type'] = $this->getApiContentType();
        }

        $headers = array_merge($headers, $additionalHeaders);

        return new Psr7\Request($method, $uri, $headers, $body);
    }

    /**
     * @return string[]
     */
    protected function getExceptionMap()
    {
        return [
            'InvalidQuery' => Exception\InvalidQueryException::class,
            'AccessTokenInvalid' => Exception\AccessTokenInvalidException::class,
            'NotFound' => Exception\NotFoundException::class,
            'RateLimitExceeded' => Exception\RateLimitExceededException::class
        ];
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * The name of the library to be used in the User-Agent header.
     *
     * @return string
     */
    abstract protected function getSdkName();

    /**
     * The version of the library to be used in the User-Agent header.
     *
     * @return string
     */
    abstract protected function getSdkVersion();

    /**
     * Returns the Content-Type (MIME-Type) to be used when communication with the API.
     *
     * @return string
     */
    abstract protected function getApiContentType();
}

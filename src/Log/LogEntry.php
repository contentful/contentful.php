<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Log;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class LogEntry implements \Serializable
{
    /**
     * @var string
     */
    private $api;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var float
     */
    private $duration;

    /**
     * @var ResponseInterface|null
     */
    private $response;

    /**
     * @var \Exception|null
     */
    private $exception;

    /**
     * LogEntry constructor.
     *
     * @param string                 $api
     * @param RequestInterface       $request
     * @param float|null             $duration
     * @param ResponseInterface|null $response
     * @param \Exception|null        $exception
     *
     * @throws \InvalidArgumentException When $api is an unrecognized value
     */
    public function __construct($api, RequestInterface $request, $duration, ResponseInterface $response = null, \Exception $exception = null)
    {
        if (!in_array($api, ['DELIVERY', 'PREVIEW', 'MANAGEMENT'], true)) {
            throw new \InvalidArgumentException('Unknown API type "' . $api . '"');
        }

        $this->api = $api;
        $this->request = $request;
        $this->exception = $exception;
        $this->duration = $duration;
        $this->response = $response;

        if ($exception instanceof \Exception) {
            $this->modifyTrace();
        }
    }

    /**
     * Modify closures in exception stack trace, because they aren't serializable.
     */
    private function modifyTrace()
    {
        $exception = $this->getException();

        $traceProperty = (new \ReflectionClass('Exception'))->getProperty('trace');
        $traceProperty->setAccessible(true);

        do {
            $trace = $traceProperty->getValue($exception);

            array_walk_recursive($trace, function (&$value) {
                if ($value instanceof \Closure) {
                    $closureReflection = new \ReflectionFunction($value);

                    $value = sprintf(
                        '(Closure in file %s at line %s)',
                        $closureReflection->getFileName(),
                        $closureReflection->getStartLine()
                    );
                } elseif (is_object($value)) {
                    $value = sprintf('object(%s)', get_class($value));
                } elseif (is_resource($value)) {
                    $value = sprintf('resource(%s)', get_resource_type($value));
                }
            });

            $traceProperty->setValue($exception, $trace);
        } while (($exception = $exception->getPrevious()) instanceof \Exception);
    }

    /**
     * @return string
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return \Exception|null
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @return float
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @return ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * True if the requests threw an error.
     *
     * @return bool
     */
    public function isError()
    {
        return $this->exception !== null;
    }

    public function serialize()
    {
        $data = [
            'api' => $this->api,
            'duration' => $this->duration,
            'exception' => $this->exception,
            'request' => \GuzzleHttp\Psr7\str($this->request),
            'response' => null
        ];

        if ($this->response !== null) {
            $data['response'] = \GuzzleHttp\Psr7\str($this->response);
        }

        return serialize((object) $data);
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        $this->api = $data->api;
        $this->duration = $data->duration;
        $this->exception = $data->exception;
        $this->request = \GuzzleHttp\Psr7\parse_request($data->request);
        $this->response = null;

        if ($data->response !== null) {
            $this->response = \GuzzleHttp\Psr7\parse_response($data->response);
        }
    }
}

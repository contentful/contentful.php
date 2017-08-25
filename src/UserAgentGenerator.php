<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful;

class UserAgentGenerator
{
    /**
     * @var string
     */
    private $sdkName;

    /**
     * @var string
     */
    private $sdkVersion;

    /**
     * @var string|null
     */
    private $applicationName;

    /**
     * @var string|null
     */
    private $applicationVersion;

    /**
     * @var string|null
     */
    private $integrationName;

    /**
     * @var string|null
     */
    private $integrationVersion;

    /**
     * @var string
     */
    private $cachedUserAgent;

    /**
     * UserAgentGenerator constructor.
     *
     * @param string $sdkName
     * @param string $sdkVersion
     */
    public function __construct($sdkName, $sdkVersion)
    {
        $this->sdkName = $sdkName;
        $this->sdkVersion = $sdkVersion;
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

        // Reset the cached value
        $this->cachedUserAgent = null;

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

        // Reset the cached value
        $this->cachedUserAgent = null;

        return $this;
    }

    private function generate()
    {
        $possibleOperatingSystems = [
            'WINNT' => 'Windows',
            'Darwin' => 'macOS'
        ];

        $parts = [
            'app' => '',
            'integration' => '',
            'sdk' => $this->sdkName . '/' . $this->sdkVersion,
            'platform' => 'PHP/' . \PHP_MAJOR_VERSION . '.' . \PHP_MINOR_VERSION . '.' . \PHP_RELEASE_VERSION,
            'os' => isset($possibleOperatingSystems[PHP_OS]) ? $possibleOperatingSystems[PHP_OS] : 'Linux'
        ];

        if ($this->applicationName !== null) {
            $parts['app'] = $this->applicationName;
            if ($this->applicationVersion !== null) {
                $parts['app'] .= '/' . $this->applicationVersion;
            }
        }

        if ($this->integrationName !== null) {
            $parts['integration'] = $this->integrationName;
            if ($this->integrationVersion !== null) {
                $parts['integration'] .= '/' . $this->integrationVersion;
            }
        }

        $agent = '';
        foreach ($parts as $key => $value) {
            if ($value === '') {
                continue;
            }
            $agent .= $key . ' ' . $value . '; ';
        }

        $this->cachedUserAgent = trim($agent);
    }

    /**
     * Returns the value of the User-Agent header for any requests made to Contentful
     *
     * @return string
     */
    public function getUserAgent()
    {
        if ($this->cachedUserAgent === null) {
            $this->generate();
        }

        return $this->cachedUserAgent;
    }
}

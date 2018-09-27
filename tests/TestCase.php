<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Contentful\Delivery\Client;
use Contentful\Delivery\ClientOptions;
use Contentful\Tests\TestCase as BaseTestCase;
use Psr\Cache\CacheItemPoolInterface;

class TestCase extends BaseTestCase
{
    /**
     * @var CacheItemPoolInterface
     */
    protected static $cache;

    public static function setUpBeforeClass()
    {
        self::$cache = new ArrayCachePool();
    }

    /**
     * @param string $key
     *
     * @return Client
     */
    protected function getClient(string $key): Client
    {
        $config = $this->getClientConfiguration($key);

        return new Client(
            $config['token'],
            $config['space'],
            $config['environment'],
            $config['options']
        );
    }

    /**
     * @param string $key
     *
     * @return array
     */
    private function getClientConfiguration(string $key): array
    {
        $config = [
            'cfexampleapi' => [],
            'cfexampleapi_preview' => [
                'token' => 'e5e8d4c5c122cf28fc1af3ff77d28bef78a3952957f15067bbc29f2f0dde0b50',
                'options' => ClientOptions::create()
                    ->usingPreviewApi(),
            ],
            'cfexampleapi_cache' => [
                'options' => ClientOptions::create()
                    ->withCache(self::$cache),
            ],
            'cfexampleapi_cache_autowarmup' => [
                'options' => ClientOptions::create()
                    ->withCache(self::$cache, \true),
            ],
            'cfexampleapi_cache_autowarmup_content' => [
                'options' => ClientOptions::create()
                    ->withCache(self::$cache, \true, \true),
            ],
            'cfexampleapi_tlh' => [
                'options' => ClientOptions::create()
                    ->withDefaultLocale('tlh'),
            ],
            'cfexampleapi_invalid' => [
                'token' => 'e5e8d4c5c122cf28fc1af3ff77d28bef78a3952957f15067bbc29f2f0dde0b50',
            ],
            '88dyiqcr7go8' => [
                'token' => '668efbfd9e398181166dec5df5a500aded96dbca2916646a3c7ec37082a7b756',
                'space' => '88dyiqcr7go8',
            ],
            '88dyiqcr7go8_preview' => [
                'token' => '81c469d7241ca02349388602dfc14107157063a6901c378a56e1835d688970bf',
                'space' => '88dyiqcr7go8',
                'options' => ClientOptions::create()
                    ->usingPreviewApi(),
            ],
            'bc32cj3kyfet_preview' => [
                'token' => '8740056d546471e0640d189615470cc12ce2d3188332352ecfb53edac59c4963',
                'space' => 'bc32cj3kyfet',
                'options' => ClientOptions::create()
                    ->usingPreviewApi(),
            ],
        ];

        if (!isset($config[$key])) {
            throw new \InvalidArgumentException(\sprintf(
                'Key "%s" is not a valid value.',
                $key
            ));
        }

        $defaultOptions = ClientOptions::create();
        if ($testingUrl = \getenv('CONTENTFUL_CDA_SDK_TESTING_URL')) {
            $defaultOptions->withHost($testingUrl);
        }

        $default = [
            'token' => 'b4c0n73n7fu1',
            'space' => 'cfexampleapi',
            'environment' => 'master',
            'options' => $defaultOptions,
        ];

        return \array_merge($default, $config[$key]);
    }

    protected function skipIfApiCoverage()
    {
        if (\getenv('CONTENTFUL_CDA_SDK_TESTING_URL')) {
            $this->markTestSkipped('This configuration blocks tests that should not be run when in the coverage proxy environment.');
        }
    }
}

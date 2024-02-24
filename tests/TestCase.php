<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery;

use Contentful\Delivery\Client;
use Contentful\Delivery\Client\JsonDecoderClientInterface;
use Contentful\Delivery\ClientOptions;
use Contentful\Tests\TestCase as BaseTestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class TestCase extends BaseTestCase
{
    public const ENV_VAR_HOST = 'CONTENTFUL_CDA_SDK_TESTING_URL';

    /**
     * @var CacheItemPoolInterface
     */
    protected static $cache;

    public static function setUpBeforeClass(): void
    {
        self::$cache = new ArrayAdapter();
    }

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

    private function getClientConfiguration(string $key): array
    {
        $config = [
            'default' => [],
            'default_preview' => [
                'token' => 'e5e8d4c5c122cf28fc1af3ff77d28bef78a3952957f15067bbc29f2f0dde0b50',
                'options' => ClientOptions::create()
                    ->usingPreviewApi(),
            ],
            'default_cache' => [
                'options' => ClientOptions::create()
                    ->withCache(self::$cache),
            ],
            'default_cache_autowarmup' => [
                'options' => ClientOptions::create()
                    ->withCache(self::$cache, true),
            ],
            'default_cache_autowarmup_content' => [
                'options' => ClientOptions::create()
                    ->withCache(self::$cache, true, true),
            ],
            'default_klingon' => [
                'options' => ClientOptions::create()
                    ->withDefaultLocale('tlh'),
            ],
            'default_invalid' => [
                'token' => 'e5e8d4c5c122cf28fc1af3ff77d28bef78a3952957f15067bbc29f2f0dde0b50',
            ],
            'new' => [
                'token' => '668efbfd9e398181166dec5df5a500aded96dbca2916646a3c7ec37082a7b756',
                'space' => '88dyiqcr7go8',
            ],
            'new_preview' => [
                'token' => '81c469d7241ca02349388602dfc14107157063a6901c378a56e1835d688970bf',
                'space' => '88dyiqcr7go8',
                'options' => ClientOptions::create()
                    ->usingPreviewApi(),
            ],
            'new_limited_permissions' => [
                'token' => '8857df140f638519fc7218095a6711d8baa6db8b303797e6d9900e46ced2a7ea',
                'space' => '88dyiqcr7go8',
                'environment' => 'secondary-environment',
            ],
            'rate_limit' => [
                'token' => '8740056d546471e0640d189615470cc12ce2d3188332352ecfb53edac59c4963',
                'space' => 'bc32cj3kyfet',
                'options' => ClientOptions::create()
                    ->usingPreviewApi(),
            ],
            'low_memory' => [
                'token' => '265b7a9d3307c5019d1d5ca97dab8e7e06d46d2e16d5d6bf584d5981cb3471c2',
                'space' => 'rtei2u35b4tn',
                'options' => ClientOptions::create()
                    ->withLowMemoryResourcePool(),
            ],
            'default_cache_query' => [
                'options' => ClientOptions::create()
                    ->withQueryCache(self::$cache, 60),
            ],
            '2_seconds_lifetime_cache_query' => [
                'options' => ClientOptions::create()
                    ->withQueryCache(self::$cache, 2),
            ],
        ];

        if (!isset($config[$key])) {
            throw new \InvalidArgumentException(sprintf('Key "%s" is not a valid value.', $key));
        }

        $defaultOptions = ClientOptions::create();
        if ($testingUrl = getenv(self::ENV_VAR_HOST)) {
            $defaultOptions->withHost($testingUrl);
        }

        $default = [
            'token' => 'b4c0n73n7fu1',
            'space' => 'cfexampleapi',
            'environment' => 'master',
            'options' => $defaultOptions,
        ];

        return array_merge($default, $config[$key]);
    }

    protected function getJsonDecoderClient(
        string $spaceId,
        string $environment = 'master',
        ?ClientOptions $options = null
    ): JsonDecoderClientInterface {
        return new Client('irrelevant', $spaceId, $environment, $options);
    }

    protected function getHost(string $default = 'https://cdn.contentful.com/'): string
    {
        return getenv(self::ENV_VAR_HOST) ?: $default;
    }

    protected function skipIfApiCoverage()
    {
        if (getenv(self::ENV_VAR_HOST)) {
            $this->markTestSkipped('This configuration blocks tests that should not be run when in the coverage proxy environment.');
        }
    }

    protected function runOnApiCoverage(): bool
    {
        if (!getenv(self::ENV_VAR_HOST)) {
            $this->markTestAsPassed();

            return false;
        }

        return true;
    }
}

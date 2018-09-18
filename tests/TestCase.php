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
    protected function getClient($key)
    {
        $testingUrl = \getenv('CONTENTFUL_CDA_SDK_TESTING_URL');
        $options = new ClientOptions();
        if ($testingUrl) {
            $options = $options->withHost($testingUrl);
        }

        switch ($key) {
            case 'cfexampleapi':
                return new Client('b4c0n73n7fu1', 'cfexampleapi', 'master', $options);
            case 'cfexampleapi_preview':
                return new Client('e5e8d4c5c122cf28fc1af3ff77d28bef78a3952957f15067bbc29f2f0dde0b50', 'cfexampleapi', 'master', $options->usingPreviewApi());
            case 'cfexampleapi_cache':
                return new Client('b4c0n73n7fu1', 'cfexampleapi', 'master', $options->withCache(self::$cache));
            case 'cfexampleapi_cache_autowarmup':
                return new Client('b4c0n73n7fu1', 'cfexampleapi', 'master', $options->withCache(self::$cache, \true));
            case 'cfexampleapi_cache_autowarmup_content':
                return new Client('b4c0n73n7fu1', 'cfexampleapi', 'master', $options->withCache(self::$cache, \true, \true));
            case 'cfexampleapi_tlh':
                return new Client('b4c0n73n7fu1', 'cfexampleapi', 'master', $options->withDefaultLocale('tlh'));
            case 'cfexampleapi_invalid':
                return new Client('e5e8d4c5c122cf28fc1af3ff77d28bef78a3952957f15067bbc29f2f0dde0b50', 'cfexampleapi', 'master', $options);
            case '88dyiqcr7go8':
                return new Client('668efbfd9e398181166dec5df5a500aded96dbca2916646a3c7ec37082a7b756', '88dyiqcr7go8', 'master', $options);
            case '88dyiqcr7go8_preview':
                return new Client('81c469d7241ca02349388602dfc14107157063a6901c378a56e1835d688970bf', '88dyiqcr7go8', 'master', $options->usingPreviewApi());
            case 'bc32cj3kyfet_preview':
                return new Client('8740056d546471e0640d189615470cc12ce2d3188332352ecfb53edac59c4963', 'bc32cj3kyfet', 'master', $options->usingPreviewApi());
            default:
                throw new \InvalidArgumentException(\sprintf(
                    'Key "%s" is not a valid value.',
                    $key
                ));
        }
    }

    protected function checkRequirements()
    {
        if (!\getenv('CONTENTFUL_CDA_SDK_TESTING_URL')) {
            return parent::checkRequirements();
        }

        $annotations = $this->getAnnotations();

        foreach (['class', 'method'] as $depth) {
            if (empty($annotations[$depth]['requires'])) {
                continue;
            }

            $requires = \array_flip($annotations[$depth]['requires']);

            if (isset($requires['API no-coverage-proxy'])) {
                return $this->markTestSkipped('This configuration blocks tests that should not be run when in the coverage proxy environment.');
            }
        }
    }
}

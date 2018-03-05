<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Contentful\Delivery\Client;
use Psr\Cache\CacheItemPoolInterface;

class TestCase extends \PHPUnit_Framework_TestCase
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
        $options = $testingUrl
            ? ['baseUri' => $testingUrl]
            : [];

        switch ($key) {
            case 'cfexampleapi':
                return new Client('b4c0n73n7fu1', 'cfexampleapi', false, null, $options);
            case 'cfexampleapi_preview':
                return new Client('e5e8d4c5c122cf28fc1af3ff77d28bef78a3952957f15067bbc29f2f0dde0b50', 'cfexampleapi', true, null, $options);
            case 'cfexampleapi_cache':
                return new Client('b4c0n73n7fu1', 'cfexampleapi', false, null, \array_merge($options, ['cache' => self::$cache]));
            case 'cfexampleapi_cache_autowarmup':
                return new Client('b4c0n73n7fu1', 'cfexampleapi', false, null, \array_merge($options, ['cache' => self::$cache, 'autoWarmup' => true]));
            case 'cfexampleapi_tlh':
                return new Client('b4c0n73n7fu1', 'cfexampleapi', false, 'tlh', $options);
            case 'cfexampleapi_invalid':
                return new Client('e5e8d4c5c122cf28fc1af3ff77d28bef78a3952957f15067bbc29f2f0dde0b50', 'cfexampleapi', false, null, $options);
            case '88dyiqcr7go8':
                return new Client('668efbfd9e398181166dec5df5a500aded96dbca2916646a3c7ec37082a7b756', '88dyiqcr7go8', false, null, $options);
            case '88dyiqcr7go8_preview':
                return new Client('81c469d7241ca02349388602dfc14107157063a6901c378a56e1835d688970bf', '88dyiqcr7go8', true, null, $options);
            case 'bc32cj3kyfet_preview':
                return new Client('8740056d546471e0640d189615470cc12ce2d3188332352ecfb53edac59c4963', 'bc32cj3kyfet', true, null, $options);
            default:
                throw new \InvalidArgumentException('Argument $key is not a valid value');
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

    /**
     * Creates an empty assertion (true == true).
     * This is done to mark tests that are expected to simply work (i.e. not throw exceptions).
     * As PHPUnit does not provide convenience methods for marking a test as passed,
     * we define one.
     */
    protected function markTestAsPassed()
    {
        $this->assertTrue(true, 'Test case did not throw an exception and passed.');
    }

    /**
     * @param string $file
     * @param object $object
     * @param string $message
     */
    protected function assertJsonFixtureEqualsJsonObject($file, $object, $message = '')
    {
        $dir = $this->convertClassToFixturePath(\debug_backtrace()[1]['class']);
        $this->assertJsonStringEqualsJsonFile($dir.'/'.$file, \GuzzleHttp\json_encode($object), $message);
    }

    /**
     * @param string $file
     * @param string $string
     * @param string $message
     */
    protected function assertJsonFixtureEqualsJsonString($file, $string, $message = '')
    {
        $dir = $this->convertClassToFixturePath(\debug_backtrace()[1]['class']);
        $this->assertJsonStringEqualsJsonFile($dir.'/'.$file, $string, $message);
    }

    /**
     * @param string $file
     *
     * @return string
     */
    protected function getFixtureContent($file)
    {
        $dir = $this->convertClassToFixturePath(\debug_backtrace()[1]['class']);

        return \file_get_contents($dir.'/'.$file);
    }

    /**
     * This automatically determined where to store the fixture according to the test name.
     * For instance, it will convert a the class
     * Contentful\Tests\Delivery\Unit\SystemPropertiesTest
     * to __DIR__.'/Fixtures/Unit/SystemProperties/'.$file.
     *
     * @param string $class
     *
     * @return string
     */
    private function convertClassToFixturePath($class)
    {
        $class = \str_replace(__NAMESPACE__.'\\', '', $class);
        $class = \str_replace('\\', '/', $class);
        $class = \mb_substr($class, 0, -4);

        return __DIR__.'/Fixtures/'.$class;
    }
}

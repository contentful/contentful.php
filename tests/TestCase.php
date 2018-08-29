<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Contentful\Core\Api\Link;
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
                return new Client('b4c0n73n7fu1', 'cfexampleapi', 'master', \false, \null, $options);
            case 'cfexampleapi_preview':
                return new Client('e5e8d4c5c122cf28fc1af3ff77d28bef78a3952957f15067bbc29f2f0dde0b50', 'cfexampleapi', 'master', \true, \null, $options);
            case 'cfexampleapi_cache':
                return new Client('b4c0n73n7fu1', 'cfexampleapi', 'master', \false, \null, \array_merge($options, ['cache' => self::$cache]));
            case 'cfexampleapi_cache_autowarmup':
                return new Client('b4c0n73n7fu1', 'cfexampleapi', 'master', \false, \null, \array_merge($options, ['cache' => self::$cache, 'autoWarmup' => \true]));
            case 'cfexampleapi_cache_autowarmup_content':
                return new Client('b4c0n73n7fu1', 'cfexampleapi', 'master', \false, \null, \array_merge($options, ['cache' => self::$cache, 'autoWarmup' => \true, 'cacheContent' => \true]));
            case 'cfexampleapi_tlh':
                return new Client('b4c0n73n7fu1', 'cfexampleapi', 'master', \false, 'tlh', $options);
            case 'cfexampleapi_invalid':
                return new Client('e5e8d4c5c122cf28fc1af3ff77d28bef78a3952957f15067bbc29f2f0dde0b50', 'cfexampleapi', 'master', \false, \null, $options);
            case '88dyiqcr7go8':
                return new Client('668efbfd9e398181166dec5df5a500aded96dbca2916646a3c7ec37082a7b756', '88dyiqcr7go8', 'master', \false, \null, $options);
            case '88dyiqcr7go8_preview':
                return new Client('81c469d7241ca02349388602dfc14107157063a6901c378a56e1835d688970bf', '88dyiqcr7go8', 'master', \true, \null, $options);
            case 'bc32cj3kyfet_preview':
                return new Client('8740056d546471e0640d189615470cc12ce2d3188332352ecfb53edac59c4963', 'bc32cj3kyfet', 'master', \true, \null, $options);
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

    /**
     * Creates an empty assertion (true == true).
     * This is done to mark tests that are expected to simply work (i.e. not throw exceptions).
     * As PHPUnit does not provide convenience methods for marking a test as passed,
     * we define one.
     */
    protected function markTestAsPassed()
    {
        $this->assertTrue(\true, 'Test case did not throw an exception and passed.');
    }

    /**
     * @param string $id
     * @param string $linkType
     * @param Link   $link
     * @param string $message
     */
    protected function assertLink($id, $linkType, Link $link, $message = '')
    {
        $this->assertSame($id, $link->getId(), $message);
        $this->assertSame($linkType, $link->getLinkType(), $message);
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

// Here we define a few convenice classes to avoid using mocks everywhere,
// and use actual resource classes instead.

namespace Contentful\Tests\Delivery\Unit\Resource;

use Contentful\Delivery\Client;

class MockSpace extends \Contentful\Delivery\Resource\Space
{
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    public static function withSys($id, $data = [])
    {
        return new static(\array_merge($data, [
            'sys' => new \Contentful\Delivery\SystemProperties(['id' => $id, 'type' => 'Space']),
        ]));
    }
}

class MockLocale extends \Contentful\Delivery\Resource\Locale
{
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    public static function withSys($id, $data = [])
    {
        return new static(\array_merge($data, [
            'sys' => new \Contentful\Delivery\SystemProperties(['id' => $id, 'type' => 'Locale']),
        ]));
    }
}

class MockEnvironment extends \Contentful\Delivery\Resource\Environment
{
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    public static function withSys($id, $data = [])
    {
        return new static(\array_merge($data, [
            'sys' => new \Contentful\Delivery\SystemProperties(['id' => $id, 'type' => 'Environment']),
        ]));
    }
}

class MockEntry extends \Contentful\Delivery\Resource\Entry
{
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    public static function withSys($id, $data = [])
    {
        return new static(\array_merge($data, [
            'sys' => new \Contentful\Delivery\SystemProperties(['id' => $id, 'type' => 'Entry']),
        ]));
    }

    public function setClient(Client $client = \null)
    {
        $this->client = $client;
    }
}

class MockContentType extends \Contentful\Delivery\Resource\ContentType
{
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    public static function withSys($id, $data = [])
    {
        return new static(\array_merge($data, [
            'sys' => new \Contentful\Delivery\SystemProperties(['id' => $id, 'type' => 'ContentType']),
        ]));
    }
}

class MockField extends \Contentful\Delivery\Resource\ContentType\Field
{
    public function __construct($id, $name, $type, array $data = [])
    {
        parent::__construct($id, $name, $type);

        foreach ($data as $property => $value) {
            if (\property_exists($this, $property)) {
                $this->$property = $value;
            }
        }
    }
}

class MockAsset extends \Contentful\Delivery\Resource\Asset
{
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    public static function withSys($id, $data = [])
    {
        return new static(\array_merge($data, [
            'sys' => new \Contentful\Delivery\SystemProperties(['id' => $id, 'type' => 'Asset']),
        ]));
    }
}

class MockDeletedResource extends \Contentful\Delivery\Resource\DeletedResource
{
    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}

class MockDeletedEntry extends \Contentful\Delivery\Resource\DeletedEntry
{
    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}

class MockLocalizedResource extends \Contentful\Delivery\Resource\LocalizedResource
{
    public function __construct(array $locales)
    {
        $this->initLocales($locales);
    }

    public function getLocaleFromInput($locale = \null)
    {
        return parent::getLocaleFromInput($locale);
    }

    public function walkFallbackChain(array $valueMap, $localeCode, \Contentful\Delivery\Resource\Environment $environment)
    {
        return parent::walkFallbackChain($valueMap, $localeCode, $environment);
    }

    public function jsonSerialize()
    {
        return [];
    }
}

<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery;

use Contentful\Delivery\Client;
use Contentful\Log\ArrayLogger;

class End2EndTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static $cacheDir = '';

    public static function setUpBeforeClass()
    {
        self::$cacheDir = __DIR__ . '/../build/cache';
    }

    /**
     * @param string $key
     *
     * @return Client
     */
    protected function getClient($key)
    {
        $testingUrl = getenv('CONTENTFUL_CDA_SDK_TESTING_URL');
        $options = $testingUrl
            ? ['uriOverride' => $testingUrl]
            : [];

        switch ($key) {
            case 'cfexampleapi':
                return new Client('b4c0n73n7fu1', 'cfexampleapi', false, null, $options);
            case 'cfexampleapi_preview':
                return new Client('e5e8d4c5c122cf28fc1af3ff77d28bef78a3952957f15067bbc29f2f0dde0b50', 'cfexampleapi', true, null, $options);
            case 'cfexampleapi_cache':
                return new Client('b4c0n73n7fu1', 'cfexampleapi', false, null, array_merge($options, ['cacheDir' => self::$cacheDir]));
            case 'cfexampleapi_logger':
                return new Client('b4c0n73n7fu1', 'cfexampleapi', false, null, array_merge($options, ['logger' => new ArrayLogger()]));
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
        if (!getenv('CONTENTFUL_CDA_SDK_TESTING_URL')) {
            return parent::checkRequirements();
        }

        $annotations = $this->getAnnotations();

        foreach (['class', 'method'] as $depth) {
            if (empty($annotations[$depth]['requires'])) {
                continue;
            }

            $requires = array_flip($annotations[$depth]['requires']);

            if (isset($requires['API no-coverage-proxy'])) {
                return $this->markTestSkipped('This configuration blocks tests that should not be run when in the coverage proxy environment.');
            }
        }
    }
}

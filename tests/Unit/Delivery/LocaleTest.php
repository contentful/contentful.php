<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\Delivery;

use Contentful\Delivery\Locale;

class LocaleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Contentful\Delivery\Locale::__construct
     * @covers \Contentful\Delivery\Locale::getCode
     * @covers \Contentful\Delivery\Locale::getName
     * @covers \Contentful\Delivery\Locale::getFallbackCode
     * @covers \Contentful\Delivery\Locale::isDefault
     */
    public function testGetters()
    {
        $code = 'en-US';
        $name = 'English (United States)';
        $default = true;
        $fallbackCode = null;

        $locale = new Locale($code, $name, $fallbackCode, $default);
        $this->assertEquals($code, $locale->getCode());
        $this->assertEquals($name, $locale->getName());
        $this->assertEquals($fallbackCode, $locale->getFallbackCode());
        $this->assertSame($default, $locale->isDefault());
    }

    /**
     * @covers \Contentful\Delivery\Locale::__construct
     * @covers \Contentful\Delivery\Locale::isDefault
     */
    public function testWithDefault()
    {
        $locale = new Locale('en-US', 'English (United States)', null);
        $this->assertFalse($locale->isDefault());
    }

    /**
     * @covers \Contentful\Delivery\Locale::__construct
     * @covers \Contentful\Delivery\Locale::jsonSerialize
     */
    public function testJsonSerialization()
    {
        $locale = new Locale('en-US', 'English (United States)', null);

        $this->assertJsonStringEqualsJsonString('{"code":"en-US","name":"English (United States)","default":false,"fallbackCode":null}', json_encode($locale));
    }
}

<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\Delivery;

use Contentful\Delivery\Locale;
use Contentful\Delivery\Space;
use Contentful\Delivery\SystemProperties;

class SpaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Contentful\Delivery\Space::__construct
     * @covers Contentful\Delivery\Space::getId
     * @covers Contentful\Delivery\Space::getName
     * @covers Contentful\Delivery\Space::getLocales
     * @covers Contentful\Delivery\Space::getDefaultLocale
     *
     * @uses Contentful\Delivery\Locale::__construct
     * @uses Contentful\Delivery\Locale::isDefault
     * @uses Contentful\Delivery\SystemProperties::__construct
     * @uses Contentful\Delivery\SystemProperties::getId
     */
    public function testGetter()
    {
        $localeDe = new Locale('de-DE', 'German (Germany)', true);
        $localeEn = new Locale('en-US', 'English (United States)');
        $sys = new SystemProperties('123', 'Space');

        $space = new Space('space name', [$localeEn, $localeDe], $sys);

        $this->assertSame('123', $space->getId());
        $this->assertSame('space name', $space->getName());
        $this->assertCount(2, $space->getLocales());
        $this->assertSame($localeDe, $space->getDefaultLocale());
    }

    /**
     * @covers Contentful\Delivery\Space::__construct
     * @covers Contentful\Delivery\Space::getId
     * @covers Contentful\Delivery\Space::jsonSerialize
     *
     * @uses Contentful\Delivery\Locale::__construct
     * @uses Contentful\Delivery\Locale::isDefault
     * @uses Contentful\Delivery\Locale::jsonSerialize
     * @uses Contentful\Delivery\SystemProperties::__construct
     * @uses Contentful\Delivery\SystemProperties::jsonSerialize
     */
    public function testJsonSerialization()
    {
        $localeDe = new Locale('de-DE', 'German (Germany)', true);
        $localeEn = new Locale('en-US', 'English (United States)');
        $sys = new SystemProperties('123', 'Space');

        $space = new Space('space name', [$localeEn, $localeDe], $sys);

        $this->assertJsonStringEqualsJsonString('{"sys":{"id":"123","type":"Space"},"name":"space name","locales":[{"code":"en-US","default":false,"name":"English (United States)"},{"code":"de-DE","default":true,"name":"German (Germany)"}]}', json_encode($space));
    }
}

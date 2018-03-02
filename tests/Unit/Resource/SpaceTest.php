<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery\Unit\Resource;

use Contentful\Delivery\Resource\Locale;
use Contentful\Delivery\Resource\Space;
use Contentful\Delivery\SystemProperties;

class SpaceTest extends \PHPUnit_Framework_TestCase
{
    public function testGetter()
    {
        $localeDe = new Locale('de-DE', 'German (Germany)', null, true);
        $localeEn = new Locale('en-US', 'English (United States)', 'de-DE');
        $sys = new SystemProperties('123', 'Space');

        $space = new Space('space name', [$localeEn, $localeDe], $sys);

        $this->assertSame('123', $space->getId());
        $this->assertSame('space name', $space->getName());
        $this->assertCount(2, $space->getLocales());
        $this->assertSame($localeDe, $space->getDefaultLocale());
        $this->assertSame($localeEn, $space->getLocale('en-US'));
    }

    public function testJsonSerialization()
    {
        $localeDe = new Locale('de-DE', 'German (Germany)', null, true);
        $localeEn = new Locale('en-US', 'English (United States)', 'de-DE');
        $sys = new SystemProperties('123', 'Space');

        $space = new Space('space name', [$localeEn, $localeDe], $sys);

        $this->assertJsonStringEqualsJsonString('{"sys":{"id":"123","type":"Space"},"name":"space name","locales":[{"code":"en-US","default":false,"name":"English (United States)","fallbackCode":"de-DE"},{"code":"de-DE","default":true,"name":"German (Germany)","fallbackCode":null}]}', \json_encode($space));
    }
}

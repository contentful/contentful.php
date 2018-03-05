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
use Contentful\Tests\Delivery\TestCase;

class SpaceTest extends TestCase
{
    public function testGetter()
    {
        $localeIt = new Locale('it-IT', 'Italian (Italy)', null, true);
        $localeEn = new Locale('en-US', 'English (United States)', 'it-IT');
        $sys = new SystemProperties('cfexampleapi', 'Space');

        $space = new Space('Space name', [$localeEn, $localeIt], $sys);

        $this->assertSame('cfexampleapi', $space->getId());
        $this->assertSame('Space name', $space->getName());
        $this->assertCount(2, $space->getLocales());
        $this->assertSame($localeIt, $space->getDefaultLocale());
        $this->assertSame($localeEn, $space->getLocale('en-US'));
    }

    public function testJsonSerialization()
    {
        $localeIt = new Locale('it-IT', 'Italian (Italy)', null, true);
        $localeEn = new Locale('en-US', 'English (United States)', 'it-IT');
        $sys = new SystemProperties('cfexampleapi', 'Space');

        $space = new Space('Space name', [$localeEn, $localeIt], $sys);

        $this->assertJsonFixtureEqualsJsonObject('serialize.json', $space);
    }
}

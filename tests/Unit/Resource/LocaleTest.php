<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery\Unit\Resource;

use Contentful\Delivery\Resource\Locale;
use Contentful\Tests\Delivery\TestCase;

class LocaleTest extends TestCase
{
    public function testGetters()
    {
        $code = 'en-US';
        $name = 'English (United States)';
        $default = true;
        $fallbackCode = null;

        $locale = new Locale($code, $name, $fallbackCode, $default);
        $this->assertSame($code, $locale->getCode());
        $this->assertSame($name, $locale->getName());
        $this->assertSame($fallbackCode, $locale->getFallbackCode());
        $this->assertSame($default, $locale->isDefault());
    }

    public function testWithDefault()
    {
        $locale = new Locale('en-US', 'English (United States)', null);
        $this->assertFalse($locale->isDefault());
    }

    public function testJsonSerialization()
    {
        $locale = new Locale('en-US', 'English (United States)', null);

        $this->assertJsonFixtureEqualsJsonObject('serialize.json', $locale);
    }
}

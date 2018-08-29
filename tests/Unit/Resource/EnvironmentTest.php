<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery\Unit\Resource;

use Contentful\Tests\Delivery\TestCase;

class EnvironmentTest extends TestCase
{
    public function testGetters()
    {
        $localeEn = new MockLocale([
            'code' => 'en-US',
            'name' => 'English (United States)',
            'fallbackCode' => 'it-IT',
            'default' => \false,
        ]);
        $localeIt = new MockLocale([
            'code' => 'it-IT',
            'name' => 'Italian (Italy)',
            'fallbackCode' => \null,
            'default' => \true,
        ]);
        $environment = MockEnvironment::withSys('master', ['locales' => [$localeEn, $localeIt]]);

        $this->assertSame('master', $environment->getId());
        $this->assertSame('Environment', $environment->getType());
        $this->assertCount(2, $environment->getLocales());
        $this->assertSame($localeIt, $environment->getDefaultLocale());
        $this->assertSame($localeEn, $environment->getLocale('en-US'));
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage No locale with code "invalid" exists in this environment.
     */
    public function testInvalidLocale()
    {
        MockEnvironment::withSys('master', ['locales' => []])->getLocale('invalid');
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage No locale marked as default exists in this environment.
     */
    public function testInvalidDefault()
    {
        MockEnvironment::withSys('master', ['locales' => []])->getDefaultLocale('invalid');
    }

    public function testJsonSerialize()
    {
        $localeEn = new MockLocale([
            'code' => 'en-US',
            'name' => 'English (United States)',
            'fallbackCode' => 'it-IT',
            'default' => \false,
        ]);
        $localeIt = new MockLocale([
            'code' => 'it-IT',
            'name' => 'Italian (Italy)',
            'fallbackCode' => \null,
            'default' => \true,
        ]);
        $environment = MockEnvironment::withSys('master', ['locales' => [$localeEn, $localeIt]]);

        $this->assertJsonFixtureEqualsJsonObject('serialize.json', $environment);
    }
}

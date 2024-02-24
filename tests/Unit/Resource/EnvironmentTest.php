<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Unit\Resource;

use Contentful\Tests\Delivery\Implementation\MockEnvironment;
use Contentful\Tests\Delivery\Implementation\MockLocale;
use Contentful\Tests\Delivery\TestCase;

class EnvironmentTest extends TestCase
{
    public function testGetters()
    {
        $localeEn = MockLocale::withSys('en-US', [
            'code' => 'en-US',
            'name' => 'English (United States)',
            'fallbackCode' => 'it-IT',
            'default' => false,
        ]);
        $localeIt = MockLocale::withSys('it-IT', [
            'code' => 'it-IT',
            'name' => 'Italian (Italy)',
            'fallbackCode' => null,
            'default' => true,
        ]);
        $environment = MockEnvironment::withSys('master', ['locales' => [$localeEn, $localeIt]]);

        $this->assertSame('master', $environment->getId());
        $this->assertSame('Environment', $environment->getType());
        $this->assertCount(2, $environment->getLocales());
        $this->assertSame($localeIt, $environment->getDefaultLocale());
        $this->assertSame($localeEn, $environment->getLocale('en-US'));
    }

    public function testInvalidLocale()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No locale with code "invalid" exists in this environment.');
        MockEnvironment::withSys('master', ['locales' => []])->getLocale('invalid');
    }

    public function testInvalidDefault()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No locale marked as default exists in this environment.');
        MockEnvironment::withSys('master', ['locales' => []])
            ->getDefaultLocale()
        ;
    }

    public function testJsonSerialize()
    {
        $localeEn = MockLocale::withSys('en-US', [
            'code' => 'en-US',
            'name' => 'English (United States)',
            'fallbackCode' => 'it-IT',
            'default' => false,
        ]);
        $localeIt = MockLocale::withSys('it-IT', [
            'code' => 'it-IT',
            'name' => 'Italian (Italy)',
            'fallbackCode' => null,
            'default' => true,
        ]);
        $environment = MockEnvironment::withSys('master', ['locales' => [$localeEn, $localeIt]]);

        $this->assertJsonFixtureEqualsJsonObject('serialize.json', $environment);
    }
}

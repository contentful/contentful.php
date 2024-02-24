<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Unit\Resource;

use Contentful\Tests\Delivery\Implementation\MockLocale;
use Contentful\Tests\Delivery\TestCase;

class LocaleTest extends TestCase
{
    public function testGetters()
    {
        $locale = MockLocale::withSys('en-US', [
            'code' => 'en-US',
            'name' => 'English (United States)',
            'fallbackCode' => null,
            'default' => true,
        ]);

        $this->assertSame('en-US', $locale->getCode());
        $this->assertSame('English (United States)', $locale->getName());
        $this->assertNull($locale->getFallbackCode());
        $this->assertTrue($locale->isDefault());
    }

    public function testWithDefault()
    {
        $locale = MockLocale::withSys('en-US', [
            'code' => 'en-US',
            'name' => 'English (United States)',
            'fallbackCode' => null,
        ]);

        $this->assertFalse($locale->isDefault());
    }

    public function testJsonSerialize()
    {
        $locale = MockLocale::withSys('en-US', [
            'code' => 'en-US',
            'name' => 'English (United States)',
            'fallbackCode' => null,
            'default' => false,
        ]);

        $this->assertJsonFixtureEqualsJsonObject('serialize.json', $locale);
    }
}

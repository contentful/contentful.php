<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery\Unit\Resource;

use Contentful\Tests\Delivery\TestCase;

class SpaceTest extends TestCase
{
    public function testGetter()
    {
        $defaultLocale = new ConcreteLocale(['code' => 'en-US', 'name' => 'English (United States)', 'default' => true]);
        $italianLocale = new ConcreteLocale(['code' => 'it-IT', 'name' => 'Italian (Italy)', 'fallbackCode' => 'en-US']);

        return ConcreteSpace::withSys('cfexampleapi', [
            'name' => 'Space name',
            'locales' => [$defaultLocale, $italianLocale],
        ]);

        $this->assertSame('cfexampleapi', $space->getId());
        $this->assertSame('Space name', $space->getName());
    }

    public function testJsonSerialize()
    {
        $defaultLocale = new ConcreteLocale(['code' => 'en-US', 'name' => 'English (United States)', 'default' => true]);
        $italianLocale = new ConcreteLocale(['code' => 'it-IT', 'name' => 'Italian (Italy)', 'fallbackCode' => 'en-US']);

        return ConcreteSpace::withSys('cfexampleapi', [
            'name' => 'Space name',
            'locales' => [$defaultLocale, $italianLocale],
        ]);

        $this->assertJsonFixtureEqualsJsonObject('serialize.json', $space);
    }
}

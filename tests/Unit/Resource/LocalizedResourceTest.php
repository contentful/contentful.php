<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery\Unit\Resource;

use Contentful\Tests\Delivery\TestCase;

class LocalizedResourceTest extends TestCase
{
    public function testGetDefaultLocale()
    {
        $resource = new MockLocalizedResource([
            new MockLocale(['code' => 'it-IT', 'name' => 'Italian (Italy)', 'fallbackCode' => 'en-US']),
            new MockLocale(['code' => 'en-US', 'name' => 'English (United States)', 'default' => true]),
        ]);

        $this->assertSame('en-US', $resource->getLocale());
    }

    public function testSetGetLocaleString()
    {
        $resource = new MockLocalizedResource([
            new MockLocale(['code' => 'it-IT', 'name' => 'Italian (Italy)', 'fallbackCode' => 'en-US']),
            new MockLocale(['code' => 'en-US', 'name' => 'English (United States)', 'default' => true]),
        ]);

        $resource->setLocale('it-IT');
        $this->assertSame('it-IT', $resource->getLocale());
    }

    public function testSetGetLocaleObject()
    {
        $itLocale = new MockLocale(['code' => 'it-IT', 'name' => 'Italian (Italy)', 'fallbackCode' => 'en-US']);

        $resource = new MockLocalizedResource([
            $itLocale,
            new MockLocale(['code' => 'en-US', 'name' => 'English (United States)', 'default' => true]),
        ]);

        $resource->setLocale($itLocale);
        $this->assertSame('it-IT', $resource->getLocale());
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Trying to switch to invalid locale "fr-FR", available locales are "it-IT, en-US".
     */
    public function testSetGetLocaleInvalid()
    {
        $resource = new MockLocalizedResource([
            new MockLocale(['code' => 'it-IT', 'name' => 'Italian (Italy)', 'fallbackCode' => 'en-US']),
            new MockLocale(['code' => 'en-US', 'name' => 'English (United States)', 'default' => true]),
        ]);

        $resource->setLocale('fr-FR');
    }

    public function testGetLocaleFromInputDefault()
    {
        $resource = new MockLocalizedResource([
            new MockLocale(['code' => 'it-IT', 'name' => 'Italian (Italy)', 'fallbackCode' => 'en-US']),
            new MockLocale(['code' => 'en-US', 'name' => 'English (United States)', 'default' => true]),
        ]);

        $this->assertSame('en-US', $resource->getLocaleFromInput());
    }

    public function testGetLocaleFromInputString()
    {
        $resource = new MockLocalizedResource([
            new MockLocale(['code' => 'it-IT', 'name' => 'Italian (Italy)', 'fallbackCode' => 'en-US']),
            new MockLocale(['code' => 'en-US', 'name' => 'English (United States)', 'default' => true]),
        ]);

        $this->assertSame('it-IT', $resource->getLocaleFromInput('it-IT'));
    }

    public function testGetLocaleFromInputObject()
    {
        $itLocale = new MockLocale(['code' => 'it-IT', 'name' => 'Italian (Italy)', 'fallbackCode' => 'en-US']);

        $resource = new MockLocalizedResource([
            $itLocale,
            new MockLocale(['code' => 'en-US', 'name' => 'English (United States)', 'default' => true]),
        ]);

        $this->assertSame('it-IT', $resource->getLocaleFromInput($itLocale));
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Trying to use invalid locale "en-GB", available locales are "it-IT, en-US".
     */
    public function testGetLocaleFromInputInvalid()
    {
        $resource = new MockLocalizedResource([
            new MockLocale(['code' => 'it-IT', 'name' => 'Italian (Italy)', 'fallbackCode' => 'en-US']),
            new MockLocale(['code' => 'en-US', 'name' => 'English (United States)', 'default' => true]),
        ]);

        $resource->getLocaleFromInput('en-GB');
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Possible endless loop when trying to walk the locale fallback chain.
     */
    public function testInfiniteLoopDetected()
    {
        $environment = new MockEnvironment([
            'locales' => [
                new MockLocale(['code' => 'it-IT', 'name' => 'Italian (Italy)', 'fallbackCode' => 'en-US']),
                new MockLocale(['code' => 'en-US', 'name' => 'English (United States)', 'fallbackCode' => 'it-IT']),
            ],
        ]);
        $resource = new MockLocalizedResource($environment->getLocales());

        $resource->loopThroughFallbackChain([], 'it-IT', $environment);
    }
}

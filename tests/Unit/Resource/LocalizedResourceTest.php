<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery\Unit\Resource;

use Contentful\Delivery\Resource\Locale;
use Contentful\Delivery\Resource\LocalizedResource;
use Contentful\Tests\Delivery\TestCase;

class LocalizedResourceTest extends TestCase
{
    public function testGetDefaultLocale()
    {
        $resource = new ConcreteLocalizedResource([
            new Locale('de-DE', 'German (Germany)', 'en-US'),
            new Locale('en-US', 'English (United States)', null, true),
        ]);

        $this->assertSame('en-US', $resource->getLocale());
    }

    public function testSetGetLocaleString()
    {
        $resource = new ConcreteLocalizedResource([
            new Locale('de-DE', 'German (Germany)', 'en-US'),
            new Locale('en-US', 'English (United States)', null, true),
        ]);

        $resource->setLocale('de-DE');
        $this->assertSame('de-DE', $resource->getLocale());
    }

    public function testSetGetLocaleObject()
    {
        $deLocale = new Locale('de-DE', 'German (Germany)', 'en-US');

        $resource = new ConcreteLocalizedResource([
            $deLocale,
            new Locale('en-US', 'English (United States)', null, true),
        ]);

        $resource->setLocale($deLocale);
        $this->assertSame('de-DE', $resource->getLocale());
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Trying to switch to invalid locale fr-FR. Available locales are de-DE, en-US.
     */
    public function testSetGetLocaleInvalid()
    {
        $resource = new ConcreteLocalizedResource([
            new Locale('de-DE', 'German (Germany)', 'en-US'),
            new Locale('en-US', 'English (United States)', null, true),
        ]);

        $resource->setLocale('fr-FR');
    }

    public function testGetLocaleFromInputDefault()
    {
        $resource = new ConcreteLocalizedResource([
            new Locale('de-DE', 'German (Germany)', 'en-US'),
            new Locale('en-US', 'English (United States)', null, true),
        ]);

        $this->assertSame('en-US', $resource->getLocaleFromInput());
    }

    public function testGetLocaleFromInputString()
    {
        $resource = new ConcreteLocalizedResource([
            new Locale('de-DE', 'German (Germany)', 'en-US'),
            new Locale('en-US', 'English (United States)', null, true),
        ]);

        $this->assertSame('de-DE', $resource->getLocaleFromInput('de-DE'));
    }

    public function testGetLocaleFromInputObject()
    {
        $deLocale = new Locale('de-DE', 'German (Germany)', 'en-US');

        $resource = new ConcreteLocalizedResource([
            $deLocale,
            new Locale('en-US', 'English (United States)', null, true),
        ]);

        $this->assertSame('de-DE', $resource->getLocaleFromInput($deLocale));
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Trying to use invalid locale en-GB. Available locales are de-DE, en-US.
     */
    public function testGetLocaleFromInputInvalid()
    {
        $resource = new ConcreteLocalizedResource([
            new Locale('de-DE', 'German (Germany)', 'en-US'),
            new Locale('en-US', 'English (United States)', null, true),
        ]);

        $resource->getLocaleFromInput('en-GB');
    }
}

class ConcreteLocalizedResource extends LocalizedResource
{
    public function getLocaleFromInput($locale = null)
    {
        return parent::getLocaleFromInput($locale);
    }

    public function jsonSerialize()
    {
        return [];
    }
}

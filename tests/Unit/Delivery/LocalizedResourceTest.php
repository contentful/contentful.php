<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\Delivery;

use Contentful\Delivery\LocalizedResource;
use Contentful\Delivery\Locale;

class LocalizedResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Contentful\Delivery\LocalizedResource::__construct
     * @covers Contentful\Delivery\LocalizedResource::getLocale
     *
     * @uses Contentful\Delivery\Locale::__construct
     * @uses Contentful\Delivery\Locale::getCode
     * @uses Contentful\Delivery\Locale::isDefault
     */
    public function testGetDefaultLocale()
    {
        $resource = new ConcreteLocalizedResource([
            new Locale('de-DE', 'German (Germany)'),
            new Locale('en-US', 'English (United States)', true)
        ]);

        $this->assertEquals('en-US', $resource->getLocale());
    }

    /**
     * @covers Contentful\Delivery\LocalizedResource::__construct
     * @covers Contentful\Delivery\LocalizedResource::setLocale
     * @covers Contentful\Delivery\LocalizedResource::getLocale
     *
     * @uses Contentful\Delivery\Locale::__construct
     * @uses Contentful\Delivery\Locale::getCode
     * @uses Contentful\Delivery\Locale::isDefault
     */
    public function testSetGetLocaleString()
    {
        $resource = new ConcreteLocalizedResource([
            new Locale('de-DE', 'German (Germany)'),
            new Locale('en-US', 'English (United States)', true)
        ]);

        $resource->setLocale('de-DE');
        $this->assertEquals('de-DE', $resource->getLocale());
    }

    /**
     * @covers Contentful\Delivery\LocalizedResource::__construct
     * @covers Contentful\Delivery\LocalizedResource::setLocale
     * @covers Contentful\Delivery\LocalizedResource::getLocale
     *
     * @uses Contentful\Delivery\Locale::__construct
     * @uses Contentful\Delivery\Locale::getCode
     * @uses Contentful\Delivery\Locale::isDefault
     */
    public function testSetGetLocaleObject()
    {
        $deLocale = new Locale('de-DE', 'German (Germany)');

        $resource = new ConcreteLocalizedResource([
            $deLocale,
            new Locale('en-US', 'English (United States)', true)
        ]);

        $resource->setLocale($deLocale);
        $this->assertEquals('de-DE', $resource->getLocale());
    }

    /**
     * @covers Contentful\Delivery\LocalizedResource::__construct
     * @covers Contentful\Delivery\LocalizedResource::setLocale
     *
     * @uses Contentful\Delivery\Locale::__construct
     * @uses Contentful\Delivery\Locale::getCode
     * @uses Contentful\Delivery\Locale::isDefault
     *
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Trying to switch to invalid locale fr-FR. Available locales are de-DE, en-US.
     */
    public function testSetGetLocaleInvalid()
    {
        $resource = new ConcreteLocalizedResource([
            new Locale('de-DE', 'German (Germany)'),
            new Locale('en-US', 'English (United States)', true)
        ]);

        $resource->setLocale('fr-FR');
    }

    /**
     * @covers Contentful\Delivery\LocalizedResource::__construct
     * @covers Contentful\Delivery\LocalizedResource::getLocaleFromInput
     *
     * @uses Contentful\Delivery\Locale::__construct
     * @uses Contentful\Delivery\Locale::getCode
     * @uses Contentful\Delivery\Locale::isDefault
     */
    public function testGetLocaleFromInputDefault()
    {
        $resource = new ConcreteLocalizedResource([
            new Locale('de-DE', 'German (Germany)'),
            new Locale('en-US', 'English (United States)', true)
        ]);

        $this->assertEquals('en-US', $resource->getLocaleFromInput());
    }

    /**
     * @covers Contentful\Delivery\LocalizedResource::__construct
     * @covers Contentful\Delivery\LocalizedResource::getLocaleFromInput
     *
     * @uses Contentful\Delivery\Locale::__construct
     * @uses Contentful\Delivery\Locale::getCode
     * @uses Contentful\Delivery\Locale::isDefault
     */
    public function testGetLocaleFromInputString()
    {
        $resource = new ConcreteLocalizedResource([
            new Locale('de-DE', 'German (Germany)'),
            new Locale('en-US', 'English (United States)', true)
        ]);

        $this->assertEquals('de-DE', $resource->getLocaleFromInput('de-DE'));
    }

    /**
     * @covers Contentful\Delivery\LocalizedResource::__construct
     * @covers Contentful\Delivery\LocalizedResource::getLocaleFromInput
     *
     * @uses Contentful\Delivery\Locale::__construct
     * @uses Contentful\Delivery\Locale::getCode
     * @uses Contentful\Delivery\Locale::isDefault
     */
    public function testGetLocaleFromInputObject()
    {
        $deLocale = new Locale('de-DE', 'German (Germany)');

        $resource = new ConcreteLocalizedResource([
            $deLocale,
            new Locale('en-US', 'English (United States)', true)
        ]);

        $this->assertEquals('de-DE', $resource->getLocaleFromInput($deLocale));
    }

    /**
     * @covers Contentful\Delivery\LocalizedResource::__construct
     * @covers Contentful\Delivery\LocalizedResource::getLocaleFromInput
     *
     * @uses Contentful\Delivery\Locale::__construct
     * @uses Contentful\Delivery\Locale::getCode
     * @uses Contentful\Delivery\Locale::isDefault
     *
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Trying to use invalid locale en-GB. Available locales are de-DE, en-US.
     */
    public function testGetLocaleFromInputInvalid()
    {
        $resource = new ConcreteLocalizedResource([
            new Locale('de-DE', 'German (Germany)'),
            new Locale('en-US', 'English (United States)', true)
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
}

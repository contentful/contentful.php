<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\Delivery;

use Contentful\Delivery\ImageOptions;

class ImageOptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::getQueryString
     */
    public function testNoOptions()
    {
        $options = new ImageOptions;

        $this->assertEquals('', $options->getQueryString());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setWidth
     * @covers Contentful\Delivery\ImageOptions::getWidth
     */
    public function testGetSetWidth()
    {
        $width = 50;

        $options = new ImageOptions;
        $options->setWidth($width);

        $this->assertSame($width, $options->getWidth());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setWidth
     * @covers Contentful\Delivery\ImageOptions::getWidth
     */
    public function testGetSetWidthNull()
    {
        $width = null;

        $options = new ImageOptions;
        $options->setWidth($width);

        $this->assertSame($width, $options->getWidth());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setWidth
     * @covers Contentful\Delivery\ImageOptions::getWidth
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSetWidthNegative()
    {
        $options = new ImageOptions;
        $options->setWidth(-50);
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setWidth
     * @covers Contentful\Delivery\ImageOptions::getQueryString
     */
    public function testQueryWidth()
    {
        $options = new ImageOptions;
        $options->setWidth(50);

        $this->assertSame('w=50', $options->getQueryString());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setHeight
     * @covers Contentful\Delivery\ImageOptions::getHeight
     */
    public function testGetSetHeight()
    {
        $height = 50;

        $options = new ImageOptions;
        $options->setHeight($height);

        $this->assertSame($height, $options->getHeight());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setHeight
     * @covers Contentful\Delivery\ImageOptions::getHeight
     */
    public function testGetSetHeightNull()
    {
        $height = null;

        $options = new ImageOptions;
        $options->setHeight($height);

        $this->assertSame($height, $options->getHeight());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setHeight
     * @covers Contentful\Delivery\ImageOptions::getHeight
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSetHeightNegative()
    {
        $options = new ImageOptions;
        $options->setHeight(-50);
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setHeight
     * @covers Contentful\Delivery\ImageOptions::getQueryString
     */
    public function testQueryHeight()
    {
        $options = new ImageOptions;
        $options->setHeight(50);

        $this->assertSame('h=50', $options->getQueryString());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setFormat
     * @covers Contentful\Delivery\ImageOptions::getFormat
     */
    public function testGetSetFormat()
    {
        $format = 'png';

        $options = new ImageOptions;
        $options->setFormat($format);

        $this->assertSame($format, $options->getFormat());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setFormat
     * @covers Contentful\Delivery\ImageOptions::getFormat
     */
    public function testGetSetFormatNull()
    {
        $format = null;

        $options = new ImageOptions;
        $options->setFormat($format);

        $this->assertSame($format, $options->getFormat());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setFormat
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSetFormatInvalid()
    {
        $options = new ImageOptions;
        $options->setFormat('invalid');
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setFormat
     * @covers Contentful\Delivery\ImageOptions::getQueryString
     */
    public function testQueryFormat()
    {
        $options = new ImageOptions;
        $options->setFormat('png');

        $this->assertSame('fm=png', $options->getQueryString());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setQuality
     * @covers Contentful\Delivery\ImageOptions::getQuality
     */
    public function testGetSetQuality()
    {
        $quality = 50;

        $options = new ImageOptions;
        $options->setQuality($quality);

        $this->assertSame($quality, $options->getQuality());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setQuality
     * @covers Contentful\Delivery\ImageOptions::getQuality
     */
    public function testGetSetQualityNull()
    {
        $quality = null;

        $options = new ImageOptions;
        $options->setQuality($quality);

        $this->assertSame($quality, $options->getQuality());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setQuality
     * @covers Contentful\Delivery\ImageOptions::getQuality
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSetQualityNegative()
    {
        $options = new ImageOptions;
        $options->setQuality(-50);
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setQuality
     * @covers Contentful\Delivery\ImageOptions::getQueryString
     */
    public function testQueryQuality()
    {
        $options = new ImageOptions;
        $options->setQuality(50);

        $this->assertSame('fm=jpg&q=50', $options->getQueryString());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setFormat
     * @covers Contentful\Delivery\ImageOptions::setQuality
     * @covers Contentful\Delivery\ImageOptions::getFormat
     * @covers Contentful\Delivery\ImageOptions::getQueryString
     */
    public function testQueryQualityOverridesFormat()
    {
        $options = new ImageOptions;
        $options
            ->setFormat('png')
            ->setQuality(50);

        $this->assertSame('jpg', $options->getFormat());
        $this->assertSame('fm=jpg&q=50', $options->getQueryString());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::isProgressive
     */
    public function testGetProgressiveDefault()
    {
        $options = new ImageOptions;
        $this->assertFalse($options->isProgressive());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setProgressive
     * @covers Contentful\Delivery\ImageOptions::isProgressive
     */
    public function testGetSetProgressive()
    {
        $options = new ImageOptions;
        $options->setProgressive(true);

        $this->assertTrue($options->isProgressive());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setProgressive
     * @covers Contentful\Delivery\ImageOptions::getQueryString
     */
    public function testQueryProgressive()
    {
        $options = new ImageOptions;
        $options->setProgressive(true);

        $this->assertSame('fm=jpg&fl=progressive', $options->getQueryString());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setFormat
     * @covers Contentful\Delivery\ImageOptions::setProgressive
     * @covers Contentful\Delivery\ImageOptions::getFormat
     * @covers Contentful\Delivery\ImageOptions::getQueryString
     */
    public function testQueryProgressiveOverridesFormat()
    {
        $options = new ImageOptions;
        $options
            ->setFormat('png')
            ->setProgressive(true);;

        $this->assertSame('jpg', $options->getFormat());
        $this->assertSame('fm=jpg&fl=progressive', $options->getQueryString());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setResizeFit
     * @covers Contentful\Delivery\ImageOptions::getResizeFit
     */
    public function testGetSetResizeFit()
    {
        $options = new ImageOptions;
        $options->setResizeFit('pad');

        $this->assertEquals('pad', $options->getResizeFit());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setResizeFit
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSetResizeFitInvalid()
    {
        $options = new ImageOptions;
        $options->setResizeFit('invalid');
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setResizeFit
     * @covers Contentful\Delivery\ImageOptions::getQueryString
     */
    public function testQueryResizeFit()
    {
        $options = new ImageOptions;
        $options->setResizeFit('pad');

        $this->assertSame('fit=pad', $options->getQueryString());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setResizeFocus
     * @covers Contentful\Delivery\ImageOptions::getResizeFocus
     */
    public function testGetSetResizeFocus()
    {
        $options = new ImageOptions;
        $options->setResizeFocus('top');

        $this->assertEquals('top', $options->getResizeFocus());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setResizeFocus
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSetResizeFocusInvalid()
    {
        $options = new ImageOptions;
        $options->setResizeFocus('invalid');
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setResizeFit
     * @covers Contentful\Delivery\ImageOptions::setResizeFocus
     * @covers Contentful\Delivery\ImageOptions::getQueryString
     */
    public function testQueryResizeFocus()
    {
        $options = new ImageOptions;
        $options->setResizeFit('thumb');
        $options->setResizeFocus('top');

        $this->assertSame('fit=thumb&f=top', $options->getQueryString());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setResizeFocus
     * @covers Contentful\Delivery\ImageOptions::getQueryString
     */
    public function testQueryResizeFocusIgnoredWithoutFit()
    {
        $options = new ImageOptions;
        $options->setResizeFocus('top');

        $this->assertSame('', $options->getQueryString());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setRadius
     * @covers Contentful\Delivery\ImageOptions::getRadius
     */
    public function testGetSetRadius()
    {
        $options = new ImageOptions;
        $options->setRadius(50.3);

        $this->assertEquals(50.3, $options->getRadius());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setRadius
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSetRadiusNegative()
    {
        $options = new ImageOptions;
        $options->setRadius(-13.2);
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setRadius
     * @covers Contentful\Delivery\ImageOptions::getQueryString
     */
    public function testQueryRadius()
    {
        $options = new ImageOptions;
        $options->setRadius(50.3);

        $this->assertSame('r=50.3', $options->getQueryString());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setBackgroundColor
     * @covers Contentful\Delivery\ImageOptions::getBackgroundColor
     */
    public function testGetSetBackgroundColorSixDigits()
    {
        $options = new ImageOptions;
        $options->setBackgroundColor('#a0f326');

        $this->assertEquals('#a0f326', $options->getBackgroundColor());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setBackgroundColor
     * @covers Contentful\Delivery\ImageOptions::getBackgroundColor
     */
    public function testGetSetBackgroundColorThreeDigits()
    {
        $options = new ImageOptions;
        $options->setBackgroundColor('#0AF');

        $this->assertEquals('#0AF', $options->getBackgroundColor());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setBackgroundColor
     * @covers Contentful\Delivery\ImageOptions::getBackgroundColor
     */
    public function testGetSetBackgroundColorUpperCase()
    {
        $options = new ImageOptions;
        $options->setBackgroundColor('#A0F326');

        $this->assertEquals('#A0F326', $options->getBackgroundColor());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setBackgroundColor
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSetBackgroundColorTooShort()
    {
        $options = new ImageOptions;
        $options->setBackgroundColor('#A0F36');
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setBackgroundColor
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSetBackgroundInvalidCharacter()
    {
        $options = new ImageOptions;
        $options->setBackgroundColor('#A0H326');
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setBackgroundColor
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSetBackgroundNoHash()
    {
        $options = new ImageOptions;
        $options->setBackgroundColor('A0F326');
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setResizeFit
     * @covers Contentful\Delivery\ImageOptions::setBackgroundColor
     * @covers Contentful\Delivery\ImageOptions::getQueryString
     */
    public function testQueryBackgroundColor()
    {
        $options = new ImageOptions;
        $options->setResizeFit('pad');
        $options->setBackgroundColor('#a0f326');

        $this->assertSame('fit=pad&bg=rgb%3Aa0f326', $options->getQueryString());
    }

    /**
     * @covers Contentful\Delivery\ImageOptions::__construct
     * @covers Contentful\Delivery\ImageOptions::setWidth
     * @covers Contentful\Delivery\ImageOptions::setHeight
     * @covers Contentful\Delivery\ImageOptions::setFormat
     * @covers Contentful\Delivery\ImageOptions::setQuality
     * @covers Contentful\Delivery\ImageOptions::setProgressive
     * @covers Contentful\Delivery\ImageOptions::getQueryString
     */
    public function testQueryCombined()
    {
        $options = new ImageOptions;
        $options
            ->setWidth(30)
            ->setHeight(40)
            ->setFormat('jpg')
            ->setProgressive(true)
            ->setQuality(80);

        $this->assertSame('w=30&h=40&fm=jpg&q=80&fl=progressive', $options->getQueryString());
    }
}

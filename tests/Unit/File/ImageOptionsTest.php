<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\Delivery;

use Contentful\File\ImageOptions;

class ImageOptionsTest extends \PHPUnit_Framework_TestCase
{
    public function testNoOptions()
    {
        $options = new ImageOptions;

        $this->assertEquals('', $options->getQueryString());
    }

    public function testGetSetWidth()
    {
        $width = 50;

        $options = new ImageOptions;
        $options->setWidth($width);

        $this->assertSame($width, $options->getWidth());
    }

    public function testGetSetWidthNull()
    {
        $width = null;

        $options = new ImageOptions;
        $options->setWidth($width);

        $this->assertSame($width, $options->getWidth());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetWidthNegative()
    {
        $options = new ImageOptions;
        $options->setWidth(-50);
    }

    public function testQueryWidth()
    {
        $options = new ImageOptions;
        $options->setWidth(50);

        $this->assertSame('w=50', $options->getQueryString());
    }

    public function testGetSetHeight()
    {
        $height = 50;

        $options = new ImageOptions;
        $options->setHeight($height);

        $this->assertSame($height, $options->getHeight());
    }

    public function testGetSetHeightNull()
    {
        $height = null;

        $options = new ImageOptions;
        $options->setHeight($height);

        $this->assertSame($height, $options->getHeight());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetHeightNegative()
    {
        $options = new ImageOptions;
        $options->setHeight(-50);
    }

    public function testQueryHeight()
    {
        $options = new ImageOptions;
        $options->setHeight(50);

        $this->assertSame('h=50', $options->getQueryString());
    }

    public function testGetSetFormat()
    {
        $format = 'png';

        $options = new ImageOptions;
        $options->setFormat($format);

        $this->assertSame($format, $options->getFormat());
    }

    public function testGetSetFormatNull()
    {
        $format = null;

        $options = new ImageOptions;
        $options->setFormat($format);

        $this->assertSame($format, $options->getFormat());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetFormatInvalid()
    {
        $options = new ImageOptions;
        $options->setFormat('invalid');
    }

    public function testQueryFormat()
    {
        $options = new ImageOptions;
        $options->setFormat('png');

        $this->assertSame('fm=png', $options->getQueryString());
    }

    public function testGetSetQuality()
    {
        $quality = 50;

        $options = new ImageOptions;
        $options->setQuality($quality);

        $this->assertSame($quality, $options->getQuality());
    }

    public function testGetSetQualityNull()
    {
        $quality = null;

        $options = new ImageOptions;
        $options->setQuality($quality);

        $this->assertSame($quality, $options->getQuality());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetQualityNegative()
    {
        $options = new ImageOptions;
        $options->setQuality(-50);
    }

    public function testQueryQuality()
    {
        $options = new ImageOptions;
        $options->setQuality(50);

        $this->assertSame('fm=jpg&q=50', $options->getQueryString());
    }

    public function testQueryQualityOverridesFormat()
    {
        $options = new ImageOptions;
        $options
            ->setFormat('png')
            ->setQuality(50);

        $this->assertSame('jpg', $options->getFormat());
        $this->assertSame('fm=jpg&q=50', $options->getQueryString());
    }

    public function testGetProgressiveDefault()
    {
        $options = new ImageOptions;
        $this->assertFalse($options->isProgressive());
    }

    public function testGetSetProgressive()
    {
        $options = new ImageOptions;
        $options->setProgressive(true);

        $this->assertTrue($options->isProgressive());
    }

    public function testQueryProgressive()
    {
        $options = new ImageOptions;
        $options->setProgressive(true);

        $this->assertSame('fm=jpg&fl=progressive', $options->getQueryString());
    }

    public function testQueryProgressiveOverridesFormat()
    {
        $options = new ImageOptions;
        $options
            ->setFormat('png')
            ->setProgressive(true);

        $this->assertSame('jpg', $options->getFormat());
        $this->assertSame('fm=jpg&fl=progressive', $options->getQueryString());
    }

    public function testGetSetResizeFit()
    {
        $options = new ImageOptions;
        $options->setResizeFit('pad');

        $this->assertEquals('pad', $options->getResizeFit());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetResizeFitInvalid()
    {
        $options = new ImageOptions;
        $options->setResizeFit('invalid');
    }

    public function testQueryResizeFit()
    {
        $options = new ImageOptions;
        $options->setResizeFit('pad');

        $this->assertSame('fit=pad', $options->getQueryString());
    }

    public function testGetSetResizeFocus()
    {
        $options = new ImageOptions;
        $options->setResizeFocus('top');

        $this->assertEquals('top', $options->getResizeFocus());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetResizeFocusInvalid()
    {
        $options = new ImageOptions;
        $options->setResizeFocus('invalid');
    }

    public function testQueryResizeFocus()
    {
        $options = new ImageOptions;
        $options->setResizeFit('thumb');
        $options->setResizeFocus('top');

        $this->assertSame('fit=thumb&f=top', $options->getQueryString());
    }

    public function testQueryResizeFocusIgnoredWithoutFit()
    {
        $options = new ImageOptions;
        $options->setResizeFocus('top');

        $this->assertSame('', $options->getQueryString());
    }

    public function testGetSetRadius()
    {
        $options = new ImageOptions;
        $options->setRadius(50.3);

        $this->assertEquals(50.3, $options->getRadius());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetRadiusNegative()
    {
        $options = new ImageOptions;
        $options->setRadius(-13.2);
    }

    public function testQueryRadius()
    {
        $options = new ImageOptions;
        $options->setRadius(50.3);

        $this->assertSame('r=50.3', $options->getQueryString());
    }

    public function testGetSetBackgroundColorSixDigits()
    {
        $options = new ImageOptions;
        $options->setBackgroundColor('#a0f326');

        $this->assertEquals('#a0f326', $options->getBackgroundColor());
    }

    public function testGetSetBackgroundColorThreeDigits()
    {
        $options = new ImageOptions;
        $options->setBackgroundColor('#0AF');

        $this->assertEquals('#0AF', $options->getBackgroundColor());
    }

    public function testGetSetBackgroundColorUpperCase()
    {
        $options = new ImageOptions;
        $options->setBackgroundColor('#A0F326');

        $this->assertEquals('#A0F326', $options->getBackgroundColor());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetBackgroundColorTooShort()
    {
        $options = new ImageOptions;
        $options->setBackgroundColor('#A0F36');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetBackgroundInvalidCharacter()
    {
        $options = new ImageOptions;
        $options->setBackgroundColor('#A0H326');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetBackgroundNoHash()
    {
        $options = new ImageOptions;
        $options->setBackgroundColor('A0F326');
    }

    public function testQueryBackgroundColor()
    {
        $options = new ImageOptions;
        $options->setResizeFit('pad');
        $options->setBackgroundColor('#a0f326');

        $this->assertSame('fit=pad&bg=rgb%3Aa0f326', $options->getQueryString());
    }

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

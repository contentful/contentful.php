<?php
/**
 * @copyright 2015 Contentful GmbH
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

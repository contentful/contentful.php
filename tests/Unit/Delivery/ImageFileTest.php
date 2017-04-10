<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\Delivery;

use Contentful\Delivery\ImageFile;
use Contentful\ImageOptions;

class ImageFileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImageFile
     */
    protected $file;

    public function setUp()
    {
        $this->file = new ImageFile(
            'Nyan_cat_250px_frame.png',
            'image/png',
            '//images.contentful.com/cfexampleapi/4gp6taAwW4CmSgumq2ekUm/9da0cd1936871b8d72343e895a00d611/Nyan_cat_250px_frame.png',
            12273,
            250,
            250
        );
    }

    /**
     * @covers Contentful\Delivery\ImageFile::__construct
     * @covers Contentful\Delivery\ImageFile::getUrl
     * @covers Contentful\Delivery\ImageFile::getWidth
     * @covers Contentful\Delivery\ImageFile::getHeight
     *
     * @covers Contentful\Delivery\File::__construct
     * @covers Contentful\Delivery\File::getUrl
     */
    public function testGetter()
    {
        $this->assertEquals('https://images.contentful.com/cfexampleapi/4gp6taAwW4CmSgumq2ekUm/9da0cd1936871b8d72343e895a00d611/Nyan_cat_250px_frame.png', $this->file->getUrl());
        $this->assertEquals(250, $this->file->getWidth());
        $this->assertEquals(250, $this->file->getHeight());
    }

    /**
     * @covers Contentful\Delivery\ImageFile::__construct
     * @covers Contentful\Delivery\ImageFile::getUrl
     *
     * @covers Contentful\Delivery\File::__construct
     * @covers Contentful\Delivery\File::getUrl
     */
    public function testWithImageOptions()
    {
        $stub = $this->getMockBuilder(ImageOptions::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stub->method('getQueryString')
            ->willReturn('fm=jpg&q=50');

        $this->assertEquals(
            'https://images.contentful.com/cfexampleapi/4gp6taAwW4CmSgumq2ekUm/9da0cd1936871b8d72343e895a00d611/Nyan_cat_250px_frame.png?fm=jpg&q=50',
            $this->file->getUrl($stub)
        );
    }

    /**
     * @covers Contentful\Delivery\ImageFile::__construct
     * @covers Contentful\Delivery\ImageFile::jsonSerialize
     *
     * @covers Contentful\Delivery\File::__construct
     * @covers Contentful\Delivery\File::jsonSerialize
     */
    public function testJsonSerialize()
    {
        $this->assertJsonStringEqualsJsonString(
            '{"fileName":"Nyan_cat_250px_frame.png","contentType":"image/png","details":{"image":{"width":250,"height":250},"size": 12273},"url": "//images.contentful.com/cfexampleapi/4gp6taAwW4CmSgumq2ekUm/9da0cd1936871b8d72343e895a00d611/Nyan_cat_250px_frame.png"}',
            json_encode($this->file)
        );
    }
}

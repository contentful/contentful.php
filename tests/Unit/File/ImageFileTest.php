<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\File;

use Contentful\File\ImageFile;
use Contentful\File\ImageOptions;

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

    public function testGetter()
    {
        $this->assertSame('//images.contentful.com/cfexampleapi/4gp6taAwW4CmSgumq2ekUm/9da0cd1936871b8d72343e895a00d611/Nyan_cat_250px_frame.png', $this->file->getUrl());
        $this->assertSame(250, $this->file->getWidth());
        $this->assertSame(250, $this->file->getHeight());
    }

    public function testWithImageOptions()
    {
        $stub = $this->getMockBuilder(ImageOptions::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stub->method('getQueryString')
            ->willReturn('fm=jpg&q=50');

        $this->assertSame(
            '//images.contentful.com/cfexampleapi/4gp6taAwW4CmSgumq2ekUm/9da0cd1936871b8d72343e895a00d611/Nyan_cat_250px_frame.png?fm=jpg&q=50',
            $this->file->getUrl($stub)
        );
    }

    public function testJsonSerialize()
    {
        $this->assertJsonStringEqualsJsonString(
            '{"fileName":"Nyan_cat_250px_frame.png","contentType":"image/png","details":{"image":{"width":250,"height":250},"size": 12273},"url": "//images.contentful.com/cfexampleapi/4gp6taAwW4CmSgumq2ekUm/9da0cd1936871b8d72343e895a00d611/Nyan_cat_250px_frame.png"}',
            \json_encode($this->file)
        );
    }
}

<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\Delivery;

use Contentful\Delivery\File;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var File
     */
    protected $file;

    public function setUp()
    {
        $this->file = new File(
            'Nyan_cat_250px_frame.png',
            'image/png',
            '//images.contentful.com/cfexampleapi/4gp6taAwW4CmSgumq2ekUm/9da0cd1936871b8d72343e895a00d611/Nyan_cat_250px_frame.png',
            12273
        );
    }

    /**
     * @covers Contentful\Delivery\File::__construct
     * @covers Contentful\Delivery\File::getFileName
     * @covers Contentful\Delivery\File::getContentType
     * @covers Contentful\Delivery\File::getUrl
     * @covers Contentful\Delivery\File::getSize
     */
    public function testGetter()
    {
        $this->assertEquals('Nyan_cat_250px_frame.png', $this->file->getFileName());
        $this->assertEquals('image/png', $this->file->getContentType());
        $this->assertEquals('//images.contentful.com/cfexampleapi/4gp6taAwW4CmSgumq2ekUm/9da0cd1936871b8d72343e895a00d611/Nyan_cat_250px_frame.png', $this->file->getUrl());
        $this->assertEquals(12273, $this->file->getSize());
    }

    /**
     * @covers Contentful\Delivery\File::__construct
     * @covers Contentful\Delivery\File::jsonSerialize
     */
    public function testJsonSerialize()
    {
        $this->assertJsonStringEqualsJsonString(
            '{"fileName":"Nyan_cat_250px_frame.png","contentType":"image/png","details":{"size": 12273},"url": "//images.contentful.com/cfexampleapi/4gp6taAwW4CmSgumq2ekUm/9da0cd1936871b8d72343e895a00d611/Nyan_cat_250px_frame.png"}',
            json_encode($this->file)
        );
    }
}

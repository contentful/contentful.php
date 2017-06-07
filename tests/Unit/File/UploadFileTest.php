<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\Delivery;

use Contentful\File\UploadFile;

class UploadFileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UploadFile
     */
    protected $file;

    public function setUp()
    {
        $this->file = new UploadFile(
            'Nyan_cat_250px_frame.png',
            'image/png',
            '//images.contentful.com/cfexampleapi/4gp6taAwW4CmSgumq2ekUm/9da0cd1936871b8d72343e895a00d611/Nyan_cat_250px_frame.png'
        );
    }

    public function testGetter()
    {
        $this->assertEquals('Nyan_cat_250px_frame.png', $this->file->getFileName());
        $this->assertEquals('image/png', $this->file->getContentType());
        $this->assertEquals('//images.contentful.com/cfexampleapi/4gp6taAwW4CmSgumq2ekUm/9da0cd1936871b8d72343e895a00d611/Nyan_cat_250px_frame.png', $this->file->getUpload());
    }

    public function testJsonSerialize()
    {
        $this->assertJsonStringEqualsJsonString(
            '{"fileName":"Nyan_cat_250px_frame.png","contentType":"image/png","upload": "//images.contentful.com/cfexampleapi/4gp6taAwW4CmSgumq2ekUm/9da0cd1936871b8d72343e895a00d611/Nyan_cat_250px_frame.png"}',
            json_encode($this->file)
        );
    }
}

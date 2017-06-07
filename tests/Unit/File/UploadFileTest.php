<?php
/**
 * @copyright 2015-2017 Contentful GmbH
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
            'the_great_gatsby.txt',
            'image/png',
            'https://www.example.com/the_great_gatsby.txt'
        );
    }

    public function testGetter()
    {
        $this->assertEquals('the_great_gatsby.txt', $this->file->getFileName());
        $this->assertEquals('image/png', $this->file->getContentType());
        $this->assertEquals('https://www.example.com/the_great_gatsby.txt', $this->file->getUpload());
    }

    public function testJsonSerialize()
    {
        $this->assertJsonStringEqualsJsonString(
            '{"fileName":"the_great_gatsby.txt","contentType":"image/png","upload": "https://www.example.com/the_great_gatsby.txt"}',
            json_encode($this->file)
        );
    }
}

<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\File;

use Contentful\File\RemoteUploadFile;

class RemoteUploadFileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RemoteUploadFile
     */
    protected $file;

    public function setUp()
    {
        $this->file = new RemoteUploadFile(
            'the_great_gatsby.txt',
            'image/png',
            'https://www.example.com/the_great_gatsby.txt'
        );
    }

    public function testGetter()
    {
        $this->assertSame('the_great_gatsby.txt', $this->file->getFileName());
        $this->assertSame('image/png', $this->file->getContentType());
        $this->assertSame('https://www.example.com/the_great_gatsby.txt', $this->file->getUpload());
    }

    public function testJsonSerialize()
    {
        $this->assertJsonStringEqualsJsonString(
            '{"fileName":"the_great_gatsby.txt","contentType":"image/png","upload": "https://www.example.com/the_great_gatsby.txt"}',
            \json_encode($this->file)
        );
    }
}

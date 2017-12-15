<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\Delivery;

use Contentful\File\LocalUploadFile;
use Contentful\Link;

class LocalUploadFileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LocalUploadFile
     */
    protected $file;

    public function setUp()
    {
        $this->file = new LocalUploadFile(
            'the_great_gatsby.txt',
            'image/png',
            new Link('1reper3p12RdEVfC13QsUR', 'Upload')
        );
    }

    public function testGetter()
    {
        $this->assertSame('the_great_gatsby.txt', $this->file->getFileName());
        $this->assertSame('image/png', $this->file->getContentType());
        $this->assertSame('1reper3p12RdEVfC13QsUR', $this->file->getUploadFrom()->getId());
        $this->assertSame('Upload', $this->file->getUploadFrom()->getLinkType());
    }

    public function testJsonSerialize()
    {
        $this->assertJsonStringEqualsJsonString(
            '{"fileName":"the_great_gatsby.txt","contentType":"image\/png","uploadFrom":{"sys":{"type":"Link","id":"1reper3p12RdEVfC13QsUR","linkType":"Upload"}}}',
            \json_encode($this->file)
        );
    }
}

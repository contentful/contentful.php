<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Link;
use Contentful\Delivery\Client;
use Contentful\File\UploadFile;
use Contentful\File\LocalUploadFile;

class UnprocessedFileInPreviewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client
     */
    private $client;

    public function setUp()
    {
        $this->client = new Client('81c469d7241ca02349388602dfc14107157063a6901c378a56e1835d688970bf', '88dyiqcr7go8', true);
    }

    /**
     * @vcr e2e_file_get_unprocessed_file.json
     */
    public function testGetUnprocessedFile()
    {
        $asset = $this->client->getAsset('147y8r7Fx4YSEWYAQyggui');

        $file = $asset->getFile();

        $this->assertInstanceOf(UploadFile::class, $file);
        $this->assertEquals('fitzgerald', $file->getFileName());
        $this->assertEquals(
            'https://upload.wikimedia.org/wikipedia/commons/5/5c/F_Scott_Fitzgerald_1921.jpg',
            $file->getUpload()
        );
    }

    /**
     * Files uploaded to `https://upload.contentful.com` have an expiration date.
     * This means that the file in this test will expire shortly after the creation of this fixture.
     * Although the response from the Preview API won't change, it will be impossible to process the Asset
     * using the Management API. This is irrelevant for the Delivery API, but it's good to rememember
     * when dealing with the CMA.
     *
     * @vcr e2e_file_uploaded_from_unprocessed_file.json
     */
    public function testUploadedFromFileUnprocessed()
    {
        $asset = $this->client->getAsset('lp8z7n381EmisqwMgmqW2');

        $file = $asset->getFile();

        $this->assertInstanceOf(LocalUploadFile::class, $file);
        $this->assertEquals('Contentful', $file->getFileName());
        $this->assertEquals('image/svg+xml', $file->getContentType());
        $this->assertInstanceOf(Link::class, $file->getUploadFrom());
        $this->assertEquals('Upload', $file->getUploadFrom()->getLinkType());
    }
}

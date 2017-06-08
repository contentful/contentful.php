<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Client;
use Contentful\File\UploadFile;

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
}

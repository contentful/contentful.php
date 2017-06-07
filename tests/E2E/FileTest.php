<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Client;
use Contentful\File\File;
use Contentful\File\ImageFile;
use Contentful\File\UploadFile;

class FileTest extends \PHPUnit_Framework_TestCase
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
     * @vcr e2e_file_get_regular_file.json
     */
    public function testGetRegularFile()
    {
        $asset = $this->client->getAsset('40sXgDkoROKsOsOAgSmU0W');

        $this->assertInstanceOf(File::class, $asset->getFile());
    }

    /**
     * @vcr e2e_file_get_image_file.json
     */
    public function testGetImageFile()
    {
        $asset = $this->client->getAsset('3S1ngcWajSia6I4sssQwyK');

        $this->assertInstanceOf(ImageFile::class, $asset->getFile());
    }

    /**
     * @vcr e2e_file_get_unprocessed_file.json
     */
    public function testGetUnprocessedFile()
    {
        $asset = $this->client->getAsset('147y8r7Fx4YSEWYAQyggui');

        $this->assertInstanceOf(UploadFile::class, $asset->getFile());
    }
}

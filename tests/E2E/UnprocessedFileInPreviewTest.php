<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\E2E;

use Contentful\Core\Api\Link;
use Contentful\Core\File\LocalUploadFile;
use Contentful\Core\File\RemoteUploadFile;
use Contentful\Tests\Delivery\TestCase;

class UnprocessedFileInPreviewTest extends TestCase
{
    /**
     * @vcr file_remote_upload_unprocessed.json
     */
    public function testFileRemoteUploadUnprocessed()
    {
        $this->skipIfApiCoverage();

        $client = $this->getClient('new_preview');

        $asset = $client->getAsset('147y8r7Fx4YSEWYAQyggui');

        $file = $asset->getFile();

        $this->assertInstanceOf(RemoteUploadFile::class, $file);
        $this->assertSame('fitzgerald', $file->getFileName());
        $this->assertSame(
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
     * @vcr file_local_upload_unprocessed.json
     */
    public function testFileLocalUploadUnprocessed()
    {
        $this->skipIfApiCoverage();

        $client = $this->getClient('new_preview');

        $asset = $client->getAsset('lp8z7n381EmisqwMgmqW2');

        $file = $asset->getFile();

        $this->assertInstanceOf(LocalUploadFile::class, $file);
        $this->assertSame('Contentful', $file->getFileName());
        $this->assertSame('image/svg+xml', $file->getContentType());
        $this->assertInstanceOf(Link::class, $file->getUploadFrom());
        $this->assertSame('Upload', $file->getUploadFrom()->getLinkType());
    }
}

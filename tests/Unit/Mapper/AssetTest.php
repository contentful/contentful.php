<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Unit\Mapper;

use Contentful\Core\File\File;
use Contentful\Core\File\ImageFile;
use Contentful\Core\File\LocalUploadFile;
use Contentful\Core\File\RemoteUploadFile;
use Contentful\Delivery\Mapper\Asset as Mapper;
use Contentful\Delivery\Resource\Asset;
use Contentful\Tests\Delivery\Implementation\MockClient;
use Contentful\Tests\Delivery\Implementation\MockEnvironment;
use Contentful\Tests\Delivery\Implementation\MockLocale;
use Contentful\Tests\Delivery\Implementation\MockParser;
use Contentful\Tests\Delivery\Implementation\MockResourceBuilder;
use Contentful\Tests\Delivery\Implementation\MockSpace;
use Contentful\Tests\Delivery\TestCase;

class AssetTest extends TestCase
{
    public function testMapper()
    {
        $mapper = new Mapper(
            new MockResourceBuilder(),
            new MockClient(),
            new MockParser()
        );

        $space = MockSpace::withSys('spaceId');
        $environment = MockEnvironment::withSys('environmentId', [
            'locales' => [
                MockLocale::withSys('en-US', [
                    'code' => 'en-US',
                    'name' => 'English (United States)',
                    'fallbackCode' => null,
                    'default' => true,
                ]),
                MockLocale::withSys('it-IT', [
                    'code' => 'it-IT',
                    'name' => 'Italian (Italy)',
                    'fallbackCode' => null,
                    'default' => false,
                ]),
                MockLocale::withSys('fr-FR', [
                    'code' => 'fr-FR',
                    'name' => 'French (France)',
                    'fallbackCode' => null,
                    'default' => false,
                ]),
                MockLocale::withSys('es-ES', [
                    'code' => 'es-ES',
                    'name' => 'Spanish (Spain)',
                    'fallbackCode' => null,
                    'default' => false,
                ]),
            ],
        ]);

        /** @var Asset $resource */
        $resource = $mapper->map(null, [
            'sys' => [
                'id' => 'assetId',
                'type' => 'Asset',
                'space' => $space,
                'environment' => $environment,
                'revision' => 1,
                'createdAt' => '2016-01-01T12:00:00.123Z',
                'updatedAt' => '2017-01-01T12:00:00.123Z',
            ],
            'fields' => [
                'title' => [
                    'en-US' => 'Some title',
                ],
                'description' => [
                    'en-US' => 'Some description',
                ],
                'file' => [
                    'en-US' => [
                        'fileName' => 'image.jpg',
                        'contentType' => 'image/jpeg',
                        'uploadFrom' => [
                            'sys' => [
                                'type' => 'Link',
                                'linkType' => 'Upload',
                                'id' => 'image',
                            ],
                        ],
                    ],
                    'it-IT' => [
                        'fileName' => 'image.jpg',
                        'contentType' => 'image/jpeg',
                        'upload' => 'https://www.example.com/image.jpg',
                    ],
                    'fr-FR' => [
                        'fileName' => 'image.jpg',
                        'contentType' => 'image/jpeg',
                        'url' => 'https://www.example.com/image.jpg',
                        'details' => [
                            'size' => 100,
                            'image' => [
                                'width' => 200,
                                'height' => 300,
                            ],
                        ],
                    ],
                    'es-ES' => [
                        'fileName' => 'file.txt',
                        'contentType' => 'text/plain',
                        'url' => 'https://www.example.com/file.txt',
                        'details' => [
                            'size' => 100,
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf(Asset::class, $resource);
        $this->assertSame('assetId', $resource->getId());
        $this->assertSame('Asset', $resource->getType());

        $sys = $resource->getSystemProperties();
        $this->assertSame($space, $sys->getSpace());
        $this->assertSame($environment, $sys->getEnvironment());
        $this->assertSame(1, $sys->getRevision());
        $this->assertSame('2016-01-01T12:00:00.123Z', (string) $sys->getCreatedAt());
        $this->assertSame('2017-01-01T12:00:00.123Z', (string) $sys->getUpdatedAt());

        $this->assertSame('Some title', $resource->getTitle('en-US'));
        $this->assertSame('Some description', $resource->getDescription('en-US'));

        /** @var LocalUploadFile $file */
        $file = $resource->getFile('en-US');
        $this->assertInstanceOf(LocalUploadFile::class, $file);
        $this->assertSame('image.jpg', $file->getFileName());
        $this->assertSame('image/jpeg', $file->getContentType());
        $this->assertLink('image', 'Upload', $file->getUploadFrom());

        /** @var RemoteUploadFile $file */
        $file = $resource->getFile('it-IT');
        $this->assertInstanceOf(RemoteUploadFile::class, $file);
        $this->assertSame('image.jpg', $file->getFileName());
        $this->assertSame('image/jpeg', $file->getContentType());
        $this->assertSame('https://www.example.com/image.jpg', $file->getUpload());

        /** @var ImageFile $file */
        $file = $resource->getFile('fr-FR');
        $this->assertInstanceOf(ImageFile::class, $file);
        $this->assertSame('image.jpg', $file->getFileName());
        $this->assertSame('image/jpeg', $file->getContentType());
        $this->assertSame('https://www.example.com/image.jpg', $file->getUrl());
        $this->assertSame(100, $file->getSize());
        $this->assertSame(200, $file->getWidth());
        $this->assertSame(300, $file->getHeight());

        /** @var File $file */
        $file = $resource->getFile('es-ES');
        $this->assertInstanceOf(File::class, $file);
        $this->assertSame('file.txt', $file->getFileName());
        $this->assertSame('text/plain', $file->getContentType());
        $this->assertSame('https://www.example.com/file.txt', $file->getUrl());
        $this->assertSame(100, $file->getSize());
    }
}

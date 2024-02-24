<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Unit\Resource;

use Contentful\Core\File\FileInterface;
use Contentful\Core\File\ImageFile;
use Contentful\Delivery\Resource\Asset;
use Contentful\Delivery\Resource\Environment;
use Contentful\Delivery\Resource\Space;
use Contentful\Delivery\SystemProperties\Asset as SystemProperties;
use Contentful\Tests\Delivery\Implementation\MockAsset;
use Contentful\Tests\Delivery\Implementation\MockEnvironment;
use Contentful\Tests\Delivery\Implementation\MockLocale;
use Contentful\Tests\Delivery\Implementation\MockSpace;
use Contentful\Tests\Delivery\TestCase;

class AssetTest extends TestCase
{
    /**
     * @var Space
     */
    private $space;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * @var ImageFile
     */
    private $file;

    /**
     * @var Asset
     */
    private $asset;

    private function createMockEnvironment()
    {
        $defaultLocale = new MockLocale(['code' => 'en-US', 'name' => 'English (United States)', 'default' => true]);
        $klingonLocale = new MockLocale(['code' => 'tlh', 'name' => 'Klingon', 'fallbackCode' => 'en-US']);
        $italianLocale = new MockLocale(['code' => 'it-IT', 'name' => 'Italian (Italy)', 'fallbackCode' => 'en-US']);

        return MockEnvironment::withSys('master', [
            'locales' => [$defaultLocale, $klingonLocale, $italianLocale],
        ]);
    }

    protected function setUp(): void
    {
        $this->space = MockSpace::withSys('spaceId');
        $this->environment = $this->createMockEnvironment();
        $this->file = new ImageFile(
            'Nyan_cat_250px_frame.png',
            'image/png',
            '//images.ctfassets.net/cfexampleapi/4gp6taAwW4CmSgumq2ekUm/9da0cd1936871b8d72343e895a00d611/Nyan_cat_250px_frame.png',
            12273,
            250,
            250
        );

        $sys = new SystemProperties([
            'id' => 'nyancat',
            'type' => 'Asset',
            'space' => $this->space,
            'environment' => $this->environment,
            'revision' => 1,
            'createdAt' => '2013-09-02T14:56:34.240Z',
            'updatedAt' => '2013-09-02T14:56:34.240Z',
        ]);

        $this->asset = new MockAsset([
            'sys' => $sys,
            'title' => ['en-US' => 'Nyan Cat', 'it-IT' => 'Gatto Nyan'],
            'description' => ['en-US' => 'A picture of Nyan Cat', 'it-IT' => 'Una foto del Gatto Nyan'],
            'file' => ['en-US' => $this->file],
        ]);
        $this->asset->initLocales($this->environment->getLocales());
    }

    public function testGetter()
    {
        $asset = $this->asset;

        $this->assertSame('Nyan Cat', $asset->getTitle());
        $this->assertSame('A picture of Nyan Cat', $asset->getDescription());
        $this->assertInstanceOf(FileInterface::class, $asset->getFile());
        $this->assertSame($this->file, $asset->getFile());

        $this->assertSame($this->space, $asset->getSpace());
        $this->assertSame($this->environment, $asset->getEnvironment());

        $this->assertSame('nyancat', $asset->getId());
        $sys = $asset->getSystemProperties();
        $this->assertSame(1, $sys->getRevision());
        $this->assertSame('2013-09-02T14:56:34.240Z', (string) $sys->getCreatedAt());
        $this->assertSame('2013-09-02T14:56:34.240Z', (string) $sys->getUpdatedAt());

        $this->assertLink('nyancat', 'Asset', $asset->asLink());
    }

    public function testEmptyFieldReturnsNull()
    {
        $sys = new SystemProperties([
            'id' => 'nyancat',
            'type' => 'Asset',
            'space' => $this->space,
            'environment' => $this->environment,
            'revision' => 1,
            'createdAt' => '2013-09-02T14:56:34.240Z',
            'updatedAt' => '2013-09-02T14:56:34.240Z',
        ]);

        $asset = new MockAsset([
            'sys' => $sys,
            'title' => [],
            'description' => [],
            'file' => [],
        ]);
        $asset->initLocales($this->environment->getLocales());

        $this->assertNull($asset->getTitle('en-US'));
        $this->assertNull($asset->getDescription('en-US'));
        $this->assertNull($asset->getFile('en-US'));

        $this->assertNull($asset->getTitle('it-IT'));
        $this->assertNull($asset->getDescription('it-IT'));
        $this->assertNull($asset->getFile('it-IT'));
    }

    public function testGetTitleWithLocale()
    {
        $asset = $this->asset;

        $this->assertSame('Nyan Cat', $asset->getTitle());
        $this->assertSame('Gatto Nyan', $asset->getTitle('it-IT'));
        $this->assertSame('Nyan Cat', $asset->getTitle('en-US'));
    }

    public function testGetDescriptionWithLocale()
    {
        $asset = $this->asset;

        $this->assertSame('A picture of Nyan Cat', $asset->getDescription());
        $this->assertSame('Una foto del Gatto Nyan', $asset->getDescription('it-IT'));
        $this->assertSame('A picture of Nyan Cat', $asset->getDescription('en-US'));
    }

    public function testGetTitleWithInvalidLocale()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Trying to use invalid locale "xyz", available locales are "en-US, tlh, it-IT".');
        $this->asset->getTitle('xyz');
    }

    public function testGetDescriptionWithInvalidLocale()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Trying to use invalid locale "xyz", available locales are "en-US, tlh, it-IT".');
        $this->asset->getDescription('xyz');
    }

    public function testGetDescriptionWhenNoDescription()
    {
        $sys = new SystemProperties([
            'id' => 'nyancat',
            'type' => 'Asset',
            'space' => $this->space,
            'environment' => $this->environment,
            'revision' => 1,
            'createdAt' => '2013-09-02T14:56:34.240Z',
            'updatedAt' => '2013-09-02T14:56:34.240Z',
        ]);
        $asset = new MockAsset([
            'sys' => $sys,
            'title' => ['en-US' => 'Nyan Cat'],
            'file' => ['en-US' => $this->file],
        ]);
        $asset->initLocales($this->environment->getLocales());

        $this->assertNull($asset->getDescription());
    }

    public function testGetTitleWhenNoTitle()
    {
        $sys = new SystemProperties([
            'id' => 'nyancat',
            'type' => 'Asset',
            'space' => $this->space,
            'environment' => $this->environment,
            'revision' => 1,
            'createdAt' => '2013-09-02T14:56:34.240Z',
            'updatedAt' => '2013-09-02T14:56:34.240Z',
        ]);
        $asset = new MockAsset([
            'sys' => $sys,
            'description' => ['en-US' => 'A picture of Nyan Cat'],
            'file' => ['en-US' => $this->file],
        ]);
        $asset->initLocales($this->environment->getLocales());

        $this->assertNull($asset->getTitle());
    }

    public function testJsonSerialize()
    {
        $this->assertJsonFixtureEqualsJsonObject('serialize.json', $this->asset);
    }

    public function testJsonSerializeWithoutDescription()
    {
        $sys = new SystemProperties([
            'id' => 'nyancat',
            'type' => 'Asset',
            'space' => $this->space,
            'environment' => $this->environment,
            'revision' => 1,
            'createdAt' => '2013-09-02T14:56:34.240Z',
            'updatedAt' => '2013-09-02T14:56:34.240Z',
        ]);
        $asset = new MockAsset([
            'sys' => $sys,
            'title' => ['en-US' => 'Nyan Cat'],
            'file' => ['en-US' => $this->file],
        ]);

        $this->assertJsonFixtureEqualsJsonObject('serialize_no_description.json', $asset);
    }

    public function testJsonSerializeNoLocale()
    {
        $sys = new SystemProperties([
            'id' => 'nyancat',
            'type' => 'Asset',
            'space' => $this->space,
            'environment' => $this->environment,
            'revision' => 1,
            'createdAt' => '2013-09-02T14:56:34.240Z',
            'updatedAt' => '2013-09-02T14:56:34.240Z',
            'locale' => 'en-US',
        ]);

        $asset = new MockAsset([
            'sys' => $sys,
            'title' => ['en-US' => 'Nyan Cat'],
            'description' => ['en-US' => 'A picture of Nyan Cat'],
            'file' => ['en-US' => $this->file],
        ]);

        $this->assertJsonFixtureEqualsJsonObject('serialize_no_locale.json', $asset);
    }
}

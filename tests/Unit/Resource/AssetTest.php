<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery\Unit\Resource;

use Contentful\Core\Api\DateTimeImmutable;
use Contentful\Core\File\FileInterface;
use Contentful\Core\File\ImageFile;
use Contentful\Delivery\Resource\Asset;
use Contentful\Delivery\Resource\Locale;
use Contentful\Delivery\Resource\Space;
use Contentful\Delivery\SystemProperties;
use Contentful\Tests\Delivery\TestCase;

class AssetTest extends TestCase
{
    /**
     * @var Space
     */
    private $space;

    /**
     * @var ImageFile
     */
    private $file;

    /**
     * @var Asset
     */
    private $asset;

    private function createMockSpace()
    {
        $space = $this->getMockBuilder(Space::class)
            ->disableOriginalConstructor()
            ->getMock();

        $defaultLocale = new Locale('en-US', 'English (United States)', null, true);
        $klingonLocale = new Locale('tlh', 'Klingon', 'en-US');
        $italianLocale = new Locale('it-IT', 'Italian', 'en-US');

        $space->method('getId')
            ->willReturn('cfexampleapi');
        $space->method('getLocales')
            ->willReturn([
                $defaultLocale,
                $klingonLocale,
                $italianLocale,
            ]);
        $space->method('getLocale')
            ->will(self::returnValueMap([
                ['en-US', $defaultLocale],
                ['tlh', $klingonLocale],
                ['it-IT', $italianLocale],
            ]));
        $space->method('getDefaultLocale')
            ->willReturn($defaultLocale);

        return $space;
    }

    public function setUp()
    {
        $this->space = $this->createMockSpace();
        $this->file = new ImageFile(
            'Nyan_cat_250px_frame.png',
            'image/png',
            '//images.contentful.com/cfexampleapi/4gp6taAwW4CmSgumq2ekUm/9da0cd1936871b8d72343e895a00d611/Nyan_cat_250px_frame.png',
            12273,
            250,
            250
        );

        $this->asset = new Asset(
            [
                'en-US' => 'Nyan Cat',
                'it-IT' => 'Gatto Nyan',
            ],
            [
                'en-US' => 'A picture of Nyan Cat',
                'it-IT' => 'Una foto del Gatto Nyan',
            ],
            ['en-US' => $this->file],
            new SystemProperties('nyancat', 'Asset', $this->space, null, 1, new DateTimeImmutable('2013-09-02T14:56:34.240Z'), new DateTimeImmutable('2013-09-02T14:56:34.240Z'))
        );
    }

    public function testGetter()
    {
        $asset = $this->asset;

        $this->assertSame('Nyan Cat', $asset->getTitle());
        $this->assertSame('A picture of Nyan Cat', $asset->getDescription());
        $this->assertInstanceOf(FileInterface::class, $asset->getFile());
        $this->assertSame($this->file, $asset->getFile());

        $this->assertSame('nyancat', $asset->getId());
        $this->assertSame(1, $asset->getRevision());
        $this->assertSame($this->space, $asset->getSpace());
        $this->assertSame('2013-09-02T14:56:34.240Z', (string) $asset->getCreatedAt());
        $this->assertSame('2013-09-02T14:56:34.240Z', (string) $asset->getUpdatedAt());
    }

    public function testGetTitleWithLocale()
    {
        $asset = $this->asset;

        $this->assertSame('Nyan Cat', $asset->getTitle());
        $this->assertSame('Gatto Nyan', $asset->getTitle('it-IT'));
        $this->assertSame('Nyan Cat', $asset->getTitle('en-US'));
        $this->assertSame('Nyan Cat', $asset->getTitle('tlh'));
    }

    public function testGetDescriptionWithLocale()
    {
        $asset = $this->asset;

        $this->assertSame('A picture of Nyan Cat', $asset->getDescription());
        $this->assertSame('Una foto del Gatto Nyan', $asset->getDescription('it-IT'));
        $this->assertSame('A picture of Nyan Cat', $asset->getDescription('en-US'));
        $this->assertSame('A picture of Nyan Cat', $asset->getDescription('tlh'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Trying to use invalid locale "xyz", available locales are "en-US, tlh, it-IT".
     */
    public function testGetTitleWithInvalidLocale()
    {
        $this->asset->getTitle('xyz');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Trying to use invalid locale "xyz", available locales are "en-US, tlh, it-IT".
     */
    public function testGetDescriptionWithInvalidLocale()
    {
        $this->asset->getDescription('xyz');
    }

    public function testGetDescriptionWhenNoDescription()
    {
        $asset = new Asset(
            ['en-US' => 'Nyan Cat'],
            null,
            ['en-US' => $this->file],
            new SystemProperties('nyancat', 'Asset', $this->space, null, 1, new DateTimeImmutable('2013-09-02T14:56:34.240Z'), new DateTimeImmutable('2013-09-02T14:56:34.240Z'))
        );

        $this->assertNull($asset->getDescription());
    }

    public function testGetTitleWhenNoTitle()
    {
        $asset = new Asset(
            null,
            ['en-US' => 'A picture of Nyan Cat'],
            ['en-US' => $this->file],
            new SystemProperties('nyancat', 'Asset', $this->space, null, 1, new DateTimeImmutable('2013-09-02T14:56:34.240Z'), new DateTimeImmutable('2013-09-02T14:56:34.240Z'))
        );

        $this->assertNull($asset->getTitle());
    }

    public function testJsonSerialize()
    {
        $this->assertJsonFixtureEqualsJsonObject('serialize.json', $this->asset);
    }

    public function testJsonSerializeWithoutDescription()
    {
        $asset = new Asset(
            ['en-US' => 'Nyan Cat'],
            null,
            ['en-US' => $this->file],
            new SystemProperties('nyancat', 'Asset', $this->space, null, 1, new DateTimeImmutable('2013-09-02T14:56:34.240Z'), new DateTimeImmutable('2013-09-02T14:56:34.240Z'))
        );

        $this->assertJsonFixtureEqualsJsonObject('serialize_no_description.json', $asset);
    }
}

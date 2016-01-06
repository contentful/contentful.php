<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Unit\Delivery;

use Contentful\Delivery\Asset;
use Contentful\Delivery\ImageFile;
use Contentful\Delivery\Locale;
use Contentful\Delivery\Space;
use Contentful\Delivery\SystemProperties;

class AssetTest extends \PHPUnit_Framework_TestCase
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

        $defaultLocale = new Locale('en-US', 'English (United States)', true);

        $space->method('getId')
            ->willReturn('cfexampleapi');
        $space->method('getLocales')
            ->willReturn([
                $defaultLocale,
                new Locale('tlh', 'Klingon')
            ]);
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
            (object) ['en-US' => 'Nyan Cat'],
            null,
            (object) ['en-US' => $this->file],
            new SystemProperties('nyancat', 'Asset', $this->space, null, 1, new \DateTimeImmutable('2013-09-02T14:56:34.240Z'), new \DateTimeImmutable('2013-09-02T14:56:34.240Z'))
        );
    }

    public function testGetter()
    {
        $asset = $this->asset;

        $this->assertEquals('Nyan Cat', $asset->getTitle());
        $this->assertSame($this->file, $asset->getFile());

        $this->assertEquals('nyancat', $asset->getId());
        $this->assertEquals(1, $asset->getRevision());
        $this->assertSame($this->space, $asset->getSpace());
        $this->assertEquals(new \DateTimeImmutable('2013-09-02T14:56:34.240Z'), $asset->getCreatedAt());
        $this->assertEquals(new \DateTimeImmutable('2013-09-02T14:56:34.240Z'), $asset->getUpdatedAt());
    }

    public function testJsonSerialize()
    {
        $this->assertJsonStringEqualsJsonString('{
  "fields": {
    "title": {
      "en-US": "Nyan Cat"
    },
    "file": {
      "en-US": {
        "fileName": "Nyan_cat_250px_frame.png",
        "contentType": "image/png",
        "details": {
          "image": {
            "width": 250,
            "height": 250
          },
          "size": 12273
        },
        "url": "//images.contentful.com/cfexampleapi/4gp6taAwW4CmSgumq2ekUm/9da0cd1936871b8d72343e895a00d611/Nyan_cat_250px_frame.png"
      }
    }
  },
  "sys": {
    "space": {
      "sys": {
        "type": "Link",
        "linkType": "Space",
        "id": "cfexampleapi"
      }
    },
    "type": "Asset",
    "id": "nyancat",
    "revision": 1,
    "createdAt": "2013-09-02T14:56:34.240Z",
    "updatedAt": "2013-09-02T14:56:34.240Z"
  }
}', json_encode($this->asset));
    }
}

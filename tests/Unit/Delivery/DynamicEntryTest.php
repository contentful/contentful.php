<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\Delivery;

use Contentful\Delivery\Asset;
use Contentful\Delivery\Client;
use Contentful\Delivery\ContentType;
use Contentful\Delivery\ContentTypeField;
use Contentful\Delivery\DynamicEntry;
use Contentful\Delivery\Link;
use Contentful\Delivery\Locale;
use Contentful\Delivery\Space;
use Contentful\Delivery\SystemProperties;
use Contentful\ResourceNotFoundException;

class DynamicEntryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DynamicEntry
     */
    private $entry;

    /**
     * @var Space
     */
    private $space;

    /**
     * @var ContentType
     */
    private $ct;

    public function createMockSpace()
    {
        $space = $this->getMockBuilder(Space::class)
            ->disableOriginalConstructor()
            ->getMock();

        $defaultLocale = new Locale('en-US', 'English (United States)', null, true);

        $space->method('getId')
            ->willReturn('cfexampleapi');
        $space->method('getLocales')
            ->willReturn([
                $defaultLocale,
                new Locale('tlh', 'Klingon', 'en-US')
            ]);
        $space->method('getDefaultLocale')
            ->willReturn($defaultLocale);

        return $space;
    }

    public function createTestContentType(Space $space)
    {
        return new ContentType(
            'Cat',
            'Meow.',
            [
                new ContentTypeField('name', 'Name', 'Text', null, null, null, true, true),
                new ContentTypeField('likes', 'Likes', 'Array', null, 'Symbol', false, false),
                new ContentTypeField('color', 'Color', 'Symbol', null, null, false, false),
                new ContentTypeField('bestFriend', 'Best Friend', 'Link', 'Entry', null, false, false),
                new ContentTypeField('birthday', 'Birthday', 'Date', null, null, false, false),
                new ContentTypeField('lifes', 'Lifes left', 'Integer', null, null, false, false, true),
                new ContentTypeField('lives', 'Lives left', 'Integer', null, null, false, false),
                new ContentTypeField('image', 'Image', 'Link', 'Asset', null, false, false)
            ],
            'name',
            new SystemProperties('cat', 'ContentType', $space, null, 2, new \DateTimeImmutable('2013-06-27T22:46:12.852Z'), new \DateTimeImmutable('2013-09-02T13:14:47.863Z'))
        );
    }

    public function setUp()
    {
        $this->space = $this->createMockSpace();
        $this->ct = $this->createTestContentType($this->space);

        $mockEntry = $this->getMockBuilder(DynamicEntry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEntry->method('getId')
            ->willReturn('happycat');

        $mockAsset = $this->getMockBuilder(Asset::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockAsset->method('getId')
            ->willReturn('nyancat');

        $this->entry = new DynamicEntry(
            (object) [
                'name' => (object) [
                    'en-US' => 'Nyan Cat',
                    'tlh' => 'Nyan vIghro\''
                ],
                'likes' => (object) [
                    'en-US' => ['rainbows', 'fish']
                ],
                'color' => (object) [
                    'en-US' => 'rainbow',
                ],
                'bestFriend' => (object) [
                    'en-US' => $mockEntry
                ],
                'birthday' => (object) [
                    'en-US' => new \DateTimeImmutable('2011-04-04T22:00:00+00:00')
                ],
                'lives' => (object) [
                    'en-US' =>  1337
                ],
                'image' => (object) [
                    'en-US' => $mockAsset
                ],
            ],
            new SystemProperties('nyancat', 'Entry', $this->space, $this->ct, 5, new \DateTimeImmutable('2013-06-27T22:46:19.513Z'), new \DateTimeImmutable('2013-09-04T09:19:39.027Z')),
            null
        );
    }

    public function testGetter()
    {
        $entry = $this->entry;

        $this->assertEquals('nyancat', $entry->getId());
        $this->assertEquals(5, $entry->getRevision());
        $this->assertEquals(new \DateTimeImmutable('2013-06-27T22:46:19.513Z'), $entry->getCreatedAt());
        $this->assertEquals(new \DateTimeImmutable('2013-09-04T09:19:39.027Z'), $entry->getUpdatedAt());
        $this->assertEquals($this->space, $entry->getSpace());
        $this->assertEquals($this->ct, $entry->getContentType());
        $this->assertEquals('happycat', $entry->getBestFriend()->getId());
    }

    public function testLinkResolution()
    {
        $ct = new ContentType(
            'Cat',
            'Meow.',
            [
                new ContentTypeField('name', 'Name', 'Text', null, null, null, true, true),
                new ContentTypeField('friend', 'Friend', 'Link', null, null, false, false),
            ],
            'name',
            new SystemProperties('cat', 'ContentType', $this->space, null, 2, new \DateTimeImmutable('2013-06-27T22:46:12.852Z'), new \DateTimeImmutable('2013-09-02T13:14:47.863Z'))
        );

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $crookshanksEntry = new DynamicEntry(
            (object) [
                'name' => (object) [
                    'en-US' => 'Crookshanks'
                ]
            ],
            new SystemProperties('crookshanks', 'Entry', $this->space, $ct, 5, new \DateTimeImmutable('2013-06-27T22:46:19.513Z'), new \DateTimeImmutable('2013-09-04T09:19:39.027Z')),
            $client
        );

        $garfieldEntry = new DynamicEntry(
            (object) [
                'name' => (object) [
                    'en-US' => 'Garfield'
                ],
                'friend' => (object) [
                    'en-US' => new Link('crookshanks', 'Entry')
                ]
            ],
            new SystemProperties('garfield', 'Entry', $this->space, $ct, 56, new \DateTimeImmutable('2013-06-27T22:46:19.513Z'), new \DateTimeImmutable('2013-09-04T09:19:39.027Z')),
            $client
        );

        $client->expects($this->any())
            ->method('resolveLink')
            ->willReturnCallback(function(Link $link) use ($garfieldEntry, $crookshanksEntry) {
                $id = $link->getId();

                if ($id === 'garfield') {
                    return $garfieldEntry;
                }
                if ($id === 'crookshanks') {
                    return $crookshanksEntry;
                }

                return new ResourceNotFoundException;
            });

        $this->assertSame($crookshanksEntry, $garfieldEntry->getFriend());
    }

    /**
     * Test for https://github.com/contentful/contentful.php/issues/9
     */
    public function testFieldNameIncludesId()
    {
        $ct = new ContentType(
            'Cat',
            'Meow.',
            [
                new ContentTypeField('name', 'Name', 'Text', null, null, null, true, true),
                new ContentTypeField('youTubeId', 'YouTube', 'Symbol', null, null, false, false),
            ],
            'name',
            new SystemProperties('cat', 'ContentType', $this->space, null, 2, new \DateTimeImmutable('2013-06-27T22:46:12.852Z'), new \DateTimeImmutable('2013-09-02T13:14:47.863Z'))
        );

        $entry = new DynamicEntry(
            (object) [
                'name' => (object) [
                    'en-US' => 'Test Entry'
                ],
                'youTubeId' => (object) [
                    'en-US' => 'l6xdPQ_O8e8',
                ]
            ],
            new SystemProperties('nyancat', 'Entry', $this->space, $ct, 5, new \DateTimeImmutable('2013-06-27T22:46:19.513Z'), new \DateTimeImmutable('2013-09-04T09:19:39.027Z')),
            null
        );

        $this->assertEquals('l6xdPQ_O8e8', $entry->getYouTubeId());
    }

    public function testOneToManyReferenceWithMissingEntry()
    {
        $ct = new ContentType(
            'Cat',
            'Meow.',
            [
                new ContentTypeField('name', 'Name', 'Text', null, null, null, true, true),
                new ContentTypeField('friends', 'Friends', 'Array', null, 'Link', false, false),
            ],
            'name',
            new SystemProperties('cat', 'ContentType', $this->space, null, 2, new \DateTimeImmutable('2013-06-27T22:46:12.852Z'), new \DateTimeImmutable('2013-09-02T13:14:47.863Z'))
        );

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $crookshanksEntry = new DynamicEntry(
            (object) [
                'name' => (object) [
                    'en-US' => 'Crookshanks'
                ],
                'friends' => (object) [
                    'en-US' => []
                ]
            ],
            new SystemProperties('crookshanks', 'Entry', $this->space, $ct, 5, new \DateTimeImmutable('2013-06-27T22:46:19.513Z'), new \DateTimeImmutable('2013-09-04T09:19:39.027Z')),
            $client
        );

        $garfieldEntry = new DynamicEntry(
            (object) [
                'name' => (object) [
                    'en-US' => 'Garfield'
                ],
                'friends' => (object) [
                    'en-US' => [new Link('crookshanks', 'Entry'), new Link('nyancat', 'Entry')]
                ]
            ],
            new SystemProperties('garfield', 'Entry', $this->space, $ct, 56, new \DateTimeImmutable('2013-06-27T22:46:19.513Z'), new \DateTimeImmutable('2013-09-04T09:19:39.027Z')),
            $client
        );

        $client->expects($this->any())
            ->method('resolveLink')
            ->willReturnCallback(function(Link $link) use ($garfieldEntry, $crookshanksEntry) {
                $id = $link->getId();

                if ($id === 'garfield') {
                    return $garfieldEntry;
                }
                if ($id === 'crookshanks') {
                    return $crookshanksEntry;
                }

                return new ResourceNotFoundException;
            });


        $friends = $garfieldEntry->getFriends();

        $this->assertCount(1, $friends);
        $this->assertSame($crookshanksEntry, $friends[0]);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testNonExistingMethod()
    {
        $this->entry->noSuchMethod();
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testNonExistingField()
    {
        $this->entry->getNoSuchField();
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testGetIdOnNonLinkField()
    {
        $this->entry->getBirthdayId();
    }

    public function testBasicMagicCalls()
    {
        $entry = $this->entry;

        $this->assertEquals('Nyan Cat', $entry->getName());
        $this->assertEquals(['rainbows', 'fish'], $entry->getLikes());
        $this->assertEquals('happycat', $entry->getBestFriendId());
    }

    public function testMagicGetterWithLocale()
    {
        $this->assertEquals('Nyan vIghro\'', $this->entry->getName('tlh'));
    }

    public function testJsonSerialize()
    {
        $this->assertJsonStringEqualsJsonString('{"fields":{"name":{"en-US":"Nyan Cat","tlh":"Nyan vIghro\'"},"likes":{"en-US":["rainbows","fish"]},"color":{"en-US":"rainbow"},"bestFriend":{"en-US":{"sys":{"type":"Link","linkType":"Entry","id":"happycat"}}},"birthday":{"en-US":"2011-04-04T22:00:00.000Z"},"lives":{"en-US":1337},"image":{"en-US":{"sys":{"type":"Link","linkType":"Asset","id":"nyancat"}}}},"sys":{"space":{"sys":{"type":"Link","linkType":"Space","id":"cfexampleapi"}},"type":"Entry","contentType":{"sys":{"type":"Link","linkType":"ContentType","id":"cat"}},"id":"nyancat","revision":5,"createdAt":"2013-06-27T22:46:19.513Z","updatedAt":"2013-09-04T09:19:39.027Z"}}', json_encode($this->entry));
    }
}

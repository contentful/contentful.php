<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery\Unit\Resource;

use Contentful\Core\Api\DateTimeImmutable;
use Contentful\Core\Api\Link;
use Contentful\Core\Exception\NotFoundException;
use Contentful\Delivery\Client;
use Contentful\Delivery\Resource\Asset;
use Contentful\Delivery\Resource\ContentType;
use Contentful\Delivery\Resource\ContentType\Field;
use Contentful\Delivery\Resource\Entry;
use Contentful\Delivery\Resource\Locale;
use Contentful\Delivery\Resource\Space;
use Contentful\Delivery\SystemProperties;
use Contentful\Tests\Delivery\TestCase;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;

class EntryTest extends TestCase
{
    /**
     * @var Entry
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
                new Locale('tlh', 'Klingon', 'en-US'),
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
                new Field('name', 'Name', 'Text', null, null, null, true, true),
                new Field('likes', 'Likes', 'Array', null, 'Symbol', false, false),
                new Field('color', 'Color', 'Symbol', null, null, false, false),
                new Field('bestFriend', 'Best Friend', 'Link', 'Entry', null, false, false),
                new Field('Enemy', 'Enemy', 'Link', 'Entry', null, false, false),
                new Field('birthday', 'Birthday', 'Date', null, null, false, false),
                new Field('lifes', 'Lifes left', 'Integer', null, null, false, false, false, true),
                new Field('lives', 'Lives left', 'Integer', null, null, false, false),
                new Field('image', 'Image', 'Link', 'Asset', null, false, false),
            ],
            'name',
            new SystemProperties('cat', 'ContentType', $space, null, 2, new DateTimeImmutable('2013-06-27T22:46:12.852Z'), new DateTimeImmutable('2013-09-02T13:14:47.863Z'))
        );
    }

    public function setUp()
    {
        $this->space = $this->createMockSpace();
        $this->ct = $this->createTestContentType($this->space);

        $mockEntry = $this->getMockBuilder(Entry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEntry->method('getId')
            ->willReturn('happycat');

        $mockEntryEnemy = $this->getMockBuilder(Entry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEntryEnemy->method('getId')
            ->willReturn('garfield');

        $mockAsset = $this->getMockBuilder(Asset::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockAsset->method('getId')
            ->willReturn('nyancat');

        $this->entry = new Entry(
            [
                'name' => [
                    'en-US' => 'Nyan Cat',
                    'tlh' => 'Nyan vIghro\'',
                ],
                'likes' => [
                    'en-US' => ['rainbows', 'fish'],
                ],
                'color' => [
                    'en-US' => 'rainbow',
                ],
                'bestFriend' => [
                    'en-US' => $mockEntry,
                ],
                'Enemy' => [
                    'en-US' => $mockEntryEnemy,
                ],
                'birthday' => [
                    'en-US' => new DateTimeImmutable('2011-04-04T22:00:00+00:00'),
                ],
                'lives' => [
                    'en-US' => 1337,
                ],
                'lifes' => [
                    'en-US' => 42,
                ],
                'image' => [
                    'en-US' => $mockAsset,
                ],
            ],
            new SystemProperties('nyancat', 'Entry', $this->space, $this->ct, 5, new DateTimeImmutable('2013-06-27T22:46:19.513Z'), new DateTimeImmutable('2013-09-04T09:19:39.027Z')),
            null
        );
    }

    public function testGetter()
    {
        $entry = $this->entry;

        $this->assertSame('nyancat', $entry->getId());
        $this->assertSame(5, $entry->getRevision());
        $this->assertSame('2013-06-27T22:46:19.513Z', (string) $entry->getCreatedAt());
        $this->assertSame('2013-09-04T09:19:39.027Z', (string) $entry->getUpdatedAt());
        $this->assertSame($this->space, $entry->getSpace());
        $this->assertSame($this->ct, $entry->getContentType());
        $this->assertSame('happycat', $entry->getBestFriend()->getId());
        $this->assertSame('garfield', $entry->getEnemy()->getId());
    }

    public function testIdGetter()
    {
        $entry = $this->entry;

        $this->assertSame('happycat', $entry->getBestFriendId());
        $this->assertSame('garfield', $entry->getEnemyId());
    }

    public function testAccessingDisabledField()
    {
        $this->assertSame(42, $this->entry->getLifes());
    }

    public function testLinkResolution()
    {
        $ct = new ContentType(
            'Cat',
            'Meow.',
            [
                new Field('name', 'Name', 'Text', null, null, null, true, true),
                new Field('friend', 'Friend', 'Link', null, null, false, false),
            ],
            'name',
            new SystemProperties('cat', 'ContentType', $this->space, null, 2, new DateTimeImmutable('2013-06-27T22:46:12.852Z'), new DateTimeImmutable('2013-09-02T13:14:47.863Z'))
        );

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $crookshanksEntry = new Entry(
            [
                'name' => [
                    'en-US' => 'Crookshanks',
                ],
            ],
            new SystemProperties('crookshanks', 'Entry', $this->space, $ct, 5, new DateTimeImmutable('2013-06-27T22:46:19.513Z'), new DateTimeImmutable('2013-09-04T09:19:39.027Z')),
            $client
        );

        $garfieldEntry = new Entry(
            [
                'name' => [
                    'en-US' => 'Garfield',
                ],
                'friend' => [
                    'en-US' => new Link('crookshanks', 'Entry'),
                ],
            ],
            new SystemProperties('garfield', 'Entry', $this->space, $ct, 56, new DateTimeImmutable('2013-06-27T22:46:19.513Z'), new DateTimeImmutable('2013-09-04T09:19:39.027Z')),
            $client
        );

        $client->expects($this->any())
            ->method('resolveLink')
            ->willReturnCallback(function (Link $link) use ($garfieldEntry, $crookshanksEntry) {
                $id = $link->getId();

                if ('garfield' === $id) {
                    return $garfieldEntry;
                }
                if ('crookshanks' === $id) {
                    return $crookshanksEntry;
                }

                throw new NotFoundException(new ClientException('abc', new Request('GET', '')));
            });

        $this->assertSame($crookshanksEntry, $garfieldEntry->getFriend());
    }

    /**
     * Test for https://github.com/contentful/contentful.php/issues/9.
     */
    public function testFieldNameIncludesId()
    {
        $ct = new ContentType(
            'Cat',
            'Meow.',
            [
                new Field('name', 'Name', 'Text', null, null, null, true, true),
                new Field('youTubeId', 'YouTube', 'Symbol', null, null, false, false),
            ],
            'name',
            new SystemProperties('cat', 'ContentType', $this->space, null, 2, new DateTimeImmutable('2013-06-27T22:46:12.852Z'), new DateTimeImmutable('2013-09-02T13:14:47.863Z'))
        );

        $entry = new Entry(
            [
                'name' => [
                    'en-US' => 'Test Entry',
                ],
                'youTubeId' => [
                    'en-US' => 'l6xdPQ_O8e8',
                ],
            ],
            new SystemProperties('nyancat', 'Entry', $this->space, $ct, 5, new DateTimeImmutable('2013-06-27T22:46:19.513Z'), new DateTimeImmutable('2013-09-04T09:19:39.027Z')),
            null
        );

        $this->assertSame('l6xdPQ_O8e8', $entry->getYouTubeId());
    }

    public function testOneToManyReferenceWithMissingEntry()
    {
        $ct = new ContentType(
            'Cat',
            'Meow.',
            [
                new Field('name', 'Name', 'Text', null, null, null, true, true),
                new Field('friends', 'Friends', 'Array', null, 'Link', false, false),
            ],
            'name',
            new SystemProperties('cat', 'ContentType', $this->space, null, 2, new DateTimeImmutable('2013-06-27T22:46:12.852Z'), new DateTimeImmutable('2013-09-02T13:14:47.863Z'))
        );

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $crookshanksEntry = new Entry(
            [
                'name' => [
                    'en-US' => 'Crookshanks',
                ],
                'friends' => [
                    'en-US' => [],
                ],
            ],
            new SystemProperties('crookshanks', 'Entry', $this->space, $ct, 5, new DateTimeImmutable('2013-06-27T22:46:19.513Z'), new DateTimeImmutable('2013-09-04T09:19:39.027Z')),
            $client
        );

        $garfieldEntry = new Entry(
            [
                'name' => [
                    'en-US' => 'Garfield',
                ],
                'friends' => [
                    'en-US' => [new Link('crookshanks', 'Entry'), new Link('nyancat', 'Entry')],
                ],
            ],
            new SystemProperties('garfield', 'Entry', $this->space, $ct, 56, new DateTimeImmutable('2013-06-27T22:46:19.513Z'), new DateTimeImmutable('2013-09-04T09:19:39.027Z')),
            $client
        );

        $client->expects($this->any())
            ->method('resolveLink')
            ->willReturnCallback(function (Link $link) use ($garfieldEntry, $crookshanksEntry) {
                $id = $link->getId();

                if ('garfield' === $id) {
                    return $garfieldEntry;
                }
                if ('crookshanks' === $id) {
                    return $crookshanksEntry;
                }

                throw new NotFoundException(new ClientException('abc', new Request('GET', '')));
            });

        $friends = $garfieldEntry->getFriends();

        $this->assertCount(1, $friends);
        $this->assertSame($crookshanksEntry, $friends[0]);
    }

    public function testGetIdsOfLinksArray()
    {
        $ct = new ContentType(
            'Cat',
            'Meow.',
            [
                new Field('name', 'Name', 'Text', null, null, null, true, true),
                new Field('friends', 'Friends', 'Array', null, 'Link', false, false),
            ],
            'name',
            new SystemProperties('cat', 'ContentType', $this->space, null, 2, new DateTimeImmutable('2013-06-27T22:46:12.852Z'), new DateTimeImmutable('2013-09-02T13:14:47.863Z'))
        );

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $garfieldEntry = new Entry(
            [
                'name' => [
                    'en-US' => 'Garfield',
                ],
                'friends' => [
                    'en-US' => [new Link('crookshanks', 'Entry'), new Link('nyancat', 'Entry')],
                ],
            ],
            new SystemProperties('garfield', 'Entry', $this->space, $ct, 56, new DateTimeImmutable('2013-06-27T22:46:19.513Z'), new DateTimeImmutable('2013-09-04T09:19:39.027Z')),
            $client
        );

        $this->assertSame(['crookshanks', 'nyancat'], $garfieldEntry->getFriendsId());
    }

    /**
     * @see https://github.com/contentful/contentful.php/issues/54
     */
    public function testSingleLocale()
    {
        $mockEntry = $this->getMockBuilder(Entry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEntry->method('getId')
            ->willReturn('happycat');

        $mockAsset = $this->getMockBuilder(Asset::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockAsset->method('getId')
            ->willReturn('nyancat');

        $entry = new Entry(
            [
                'name' => [
                    'tlh' => 'Nyan vIghro\'',
                ],
                'likes' => [
                    'tlh' => ['rainbows', 'fish'],
                ],
                'color' => [
                    'tlh' => 'rainbow',
                ],
                'bestFriend' => [
                    'tlh' => $mockEntry,
                ],
                'birthday' => [
                    'tlh' => new DateTimeImmutable('2011-04-04T22:00:00+00:00'),
                ],
                'lives' => [
                    'tlh' => 1337,
                ],
                'image' => [
                    'tlh' => $mockAsset,
                ],
            ],
            new SystemProperties('nyancat', 'Entry', $this->space, $this->ct, 5, new DateTimeImmutable('2013-06-27T22:46:19.513Z'), new DateTimeImmutable('2013-09-04T09:19:39.027Z')),
            null
        );
        $entry->setLocale('tlh');

        $this->assertSame(['rainbows', 'fish'], $entry->getLikes());
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testNonExistingMethod()
    {
        $this->entry->noSuchMethod();
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testNonExistingField()
    {
        $this->entry->getNoSuchField();
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testGetIdOnNonLinkField()
    {
        $this->entry->getBirthdayId();
    }

    public function testBasicMagicCalls()
    {
        $entry = $this->entry;

        $this->assertSame('Nyan Cat', $entry->getName());
        $this->assertSame(['rainbows', 'fish'], $entry->getLikes());
        $this->assertSame('happycat', $entry->getBestFriendId());
    }

    public function testMagicGetterWithLocale()
    {
        $this->assertSame('Nyan vIghro\'', $this->entry->getName('tlh'));
    }

    public function testJsonSerialize()
    {
        $this->assertJsonFixtureEqualsJsonObject('serialize.json', $this->entry);
    }
}

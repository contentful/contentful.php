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
use Contentful\Delivery\Resource\ContentType;
use Contentful\Delivery\Resource\Entry;
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
    private $contentType;

    public function createMockSpace()
    {
        $localeEn = new ConcreteLocale([
            'code' => 'en-US',
            'name' => 'English (United States)',
            'fallbackCode' => null,
            'default' => true,
        ]);
        $localeTlh = new ConcreteLocale([
            'code' => 'tlh',
            'name' => 'Klingon',
            'fallbackCode' => 'en-US',
            'default' => false,
        ]);

        return ConcreteSpace::withSys('cfexampleapi', [
            'locales' => [$localeEn, $localeTlh],
        ]);
    }

    public function createTestContentType(Space $space)
    {
        $sys = new SystemProperties([
            'id' => 'cat',
            'type' => 'ContentType',
            'space' => $space,
            'revision' => 2,
            'createdAt' => new DateTimeImmutable('2013-06-27T22:46:12.852Z'),
            'updatedAt' => new DateTimeImmutable('2013-09-02T13:14:47.863Z'),
        ]);

        return new ConcreteContentType([
            'sys' => $sys,
            'name' => 'Cat',
            'description' => 'Meow.',
            'displayField' => 'name',
            'fields' => [
                'name' => new ConcreteField(['id' => 'name', 'name' => 'Name', 'type' => 'Text', 'localized' => true, 'disabled' => true]),
                'likes' => new ConcreteField(['id' => 'likes', 'name' => 'Like', 'type' => 'Array', 'itemsType' => 'Symbol']),
                'color' => new ConcreteField(['id' => 'color', 'name' => 'Color', 'type' => 'Symbol']),
                'bestFriend' => new ConcreteField(['id' => 'bestFriend', 'name' => 'Best Friend', 'type' => 'Link', 'linkType' => 'Entry']),
                'Enemy' => new ConcreteField(['id' => 'Enemy', 'name' => 'Enemy', 'type' => 'Link', 'linkType' => 'Entry']),
                'birthday' => new ConcreteField(['id' => 'name', 'name' => 'Birthday', 'type' => 'Date']),
                'lifes' => new ConcreteField(['id' => 'lifes', 'name' => 'Lifes left', 'type' => 'Integer', 'disabled' => true]),
                'lives' => new ConcreteField(['id' => 'lives', 'name' => 'Lives left', 'type' => 'Integer']),
                'image' => new ConcreteField(['id' => 'image', 'name' => 'Image', 'type' => 'Link', 'linkType' => 'Asset']),
            ],
        ]);
    }

    public function createMockClient()
    {
        return $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function setUp()
    {
        $this->space = $this->createMockSpace();
        $this->contentType = $this->createTestContentType($this->space);

        $sys = new SystemProperties([
            'id' => 'nyancat',
            'type' => 'Entry',
            'space' => $this->space,
            'contentType' => $this->contentType,
            'revision' => 5,
            'createdAt' => new DateTimeImmutable('2013-06-27T22:46:19.513Z'),
            'updatedAt' => new DateTimeImmutable('2013-09-04T09:19:39.027Z'),
        ]);
        $this->entry = new ConcreteEntry(['sys' => $sys, 'fields' => [
            'name' => ['en-US' => 'Nyan Cat', 'tlh' => 'Nyan vIghro\''],
            'likes' => ['en-US' => ['rainbows', 'fish']],
            'color' => ['en-US' => 'rainbow'],
            'bestFriend' => ['en-US' => ConcreteEntry::withSys('happycat')],
            'Enemy' => ['en-US' => ConcreteEntry::withSys('garfield')],
            'birthday' => ['en-US' => new DateTimeImmutable('2011-04-04T22:00:00+00:00')],
            'lives' => ['en-US' => 1337],
            'lifes' => ['en-US' => 42],
            'image' => ['en-US' => ConcreteAsset::withSys('nyancat')],
        ]]);
        $this->entry->setLocales($this->space->getLocales());
    }

    public function testGetter()
    {
        $entry = $this->entry;

        $this->assertSame('nyancat', $entry->getId());
        $this->assertSame(5, $entry->getRevision());
        $this->assertSame('2013-06-27T22:46:19.513Z', (string) $entry->getCreatedAt());
        $this->assertSame('2013-09-04T09:19:39.027Z', (string) $entry->getUpdatedAt());
        $this->assertSame($this->contentType, $entry->getContentType());
        $this->assertSame('happycat', $entry->getBestFriend()->getId());
        $this->assertSame('garfield', $entry->getEnemy()->getId());

        $link = $entry->asLink();
        $this->assertInstanceOf(Link::class, $link);
        $this->assertSame('nyancat', $link->getId());
        $this->assertSame('Entry', $link->getLinkType());
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
        $sys = new SystemProperties([
            'id' => 'cat',
            'type' => 'ContentType',
            'space' => $this->space,
            'revision' => 2,
            'createdAt' => new DateTimeImmutable('2013-06-27T22:46:12.852Z'),
            'updatedAt' => new DateTimeImmutable('2013-09-02T13:14:47.863Z'),
        ]);
        $contentType = new ConcreteContentType([
            'sys' => $sys,
            'name' => 'Cat',
            'description' => 'Meow.',
            'displayField' => 'name',
            'fields' => [
                'name' => new ConcreteField(['id' => 'name', 'name' => 'Name', 'type' => 'Text', 'localized' => true, 'disabled' => true]),
                'friend' => new ConcreteField(['id' => 'friend', 'name' => 'Friend', 'type' => 'Link']),
            ],
        ]);

        $client = $this->createMockClient();

        $sys = new SystemProperties([
            'id' => 'crookshanks',
            'type' => 'Entry',
            'space' => $this->space,
            'contentType' => $contentType,
            'revision' => 5,
            'createdAt' => new DateTimeImmutable('2013-06-27T22:46:19.513Z'),
            'updatedAt' => new DateTimeImmutable('2013-09-04T09:19:39.027Z'),
        ]);
        $crookshanksEntry = new ConcreteEntry(['client' => $client, 'sys' => $sys, 'fields' => [
            'name' => ['en-US' => 'Crookshanks'],
        ]]);
        $crookshanksEntry->setLocales($this->space->getLocales());

        $sys = new SystemProperties([
            'id' => 'garfield',
            'type' => 'Entry',
            'space' => $this->space,
            'contentType' => $contentType,
            'revision' => 56,
            'createdAt' => new DateTimeImmutable('2013-06-27T22:46:19.513Z'),
            'updatedAt' => new DateTimeImmutable('2013-09-04T09:19:39.027Z'),
        ]);
        $garfieldEntry = new ConcreteEntry(['client' => $client, 'sys' => $sys, 'fields' => [
            'name' => ['en-US' => 'Garfield'],
            'friend' => ['en-US' => new Link('crookshanks', 'Entry')],
        ]]);
        $garfieldEntry->setLocales($this->space->getLocales());

        $client->expects($this->any())
            ->method('resolveLink')
            ->willReturnCallback(function (Link $link) use ($garfieldEntry, $crookshanksEntry) {
                if ('garfield' === $link->getId()) {
                    return $garfieldEntry;
                }
                if ('crookshanks' === $link->getId()) {
                    return $crookshanksEntry;
                }

                throw new NotFoundException(new ClientException('Exception message', new Request('GET', '')));
            });

        $this->assertSame($crookshanksEntry, $garfieldEntry->getFriend());
    }

    /**
     * Test for https://github.com/contentful/contentful.php/issues/9.
     */
    public function testFieldNameIncludesId()
    {
        $sys = new SystemProperties([
            'id' => 'cat',
            'type' => 'ContentType',
            'space' => $this->space,
            'revision' => 2,
            'createdAt' => new DateTimeImmutable('2013-06-27T22:46:12.852Z'),
            'updatedAt' => new DateTimeImmutable('2013-09-02T13:14:47.863Z'),
        ]);
        $contentType = new ConcreteContentType([
            'sys' => $sys,
            'name' => 'Cat',
            'description' => 'Meow.',
            'displayField' => 'name',
            'fields' => [
                'name' => new ConcreteField(['id' => 'name', 'name' => 'Name', 'type' => 'Text', 'localized' => true, 'disabled' => true]),
                'youTubeId' => new ConcreteField(['id' => 'youTubeId', 'name' => 'YouTube', 'type' => 'Symbol']),
            ],
        ]);

        $sys = new SystemProperties([
            'id' => 'nyancat',
            'type' => 'Entry',
            'space' => $this->space,
            'contentType' => $contentType,
            'revision' => 5,
            'createdAt' => new DateTimeImmutable('2013-06-27T22:46:19.513Z'),
            'updatedAt' => new DateTimeImmutable('2013-09-04T09:19:39.027Z'),
        ]);
        $entry = new ConcreteEntry(['sys' => $sys, 'fields' => [
            'name' => ['en-US' => 'Test Entry'],
            'youTubeId' => ['en-US' => 'l6xdPQ_O8e8'],
        ]]);
        $entry->setLocales($this->space->getLocales());

        $this->assertSame('l6xdPQ_O8e8', $entry->getYouTubeId());
    }

    public function testOneToManyReferenceWithMissingEntry()
    {
        $sys = new SystemProperties([
            'id' => 'cat',
            'type' => 'ContentType',
            'space' => $this->space,
            'revision' => 2,
            'createdAt' => new DateTimeImmutable('2013-06-27T22:46:12.852Z'),
            'updatedAt' => new DateTimeImmutable('2013-09-02T13:14:47.863Z'),
        ]);
        $contentType = new ConcreteContentType([
            'sys' => $sys,
            'name' => 'Cat',
            'description' => 'Meow.',
            'displayField' => 'name',
            'fields' => [
                'name' => new ConcreteField(['id' => 'name', 'name' => 'Name', 'type' => 'Text', 'localized' => true, 'disabled' => true]),
                'friends' => new ConcreteField(['id' => 'friends', 'name' => 'Friends', 'type' => 'Array', 'itemsType' => 'Link']),
            ],
        ]);

        $client = $this->createMockClient();

        $sys = new SystemProperties([
            'id' => 'crookshanks',
            'type' => 'Entry',
            'space' => $this->space,
            'contentType' => $contentType,
            'revision' => 5,
            'createdAt' => new DateTimeImmutable('2013-06-27T22:46:19.513Z'),
            'updatedAt' => new DateTimeImmutable('2013-09-04T09:19:39.027Z'),
        ]);
        $crookshanksEntry = new ConcreteEntry(['client' => $client, 'sys' => $sys, 'fields' => [
            'name' => ['en-US' => 'Crookshanks'],
            'friends' => ['en-US' => []],
        ]]);
        $crookshanksEntry->setLocales($this->space->getLocales());

        $sys = new SystemProperties([
            'id' => 'garfield',
            'type' => 'Entry',
            'space' => $this->space,
            'contentType' => $contentType,
            'revision' => 56,
            'createdAt' => new DateTimeImmutable('2013-06-27T22:46:19.513Z'),
            'updatedAt' => new DateTimeImmutable('2013-09-04T09:19:39.027Z'),
        ]);
        $garfieldEntry = new ConcreteEntry(['client' => $client, 'sys' => $sys, 'fields' => [
            'name' => ['en-US' => 'Garfield'],
            'friends' => ['en-US' => [new Link('crookshanks', 'Entry'), new Link('nyancat', 'Entry')]],
        ]]);
        $garfieldEntry->setLocales($this->space->getLocales());

        $client->expects($this->any())
            ->method('resolveLink')
            ->willReturnCallback(function (Link $link) use ($garfieldEntry, $crookshanksEntry) {
                if ('garfield' === $link->getId()) {
                    return $garfieldEntry;
                }
                if ('crookshanks' === $link->getId()) {
                    return $crookshanksEntry;
                }

                throw new NotFoundException(new ClientException('Exception message', new Request('GET', '')));
            });

        $friends = $garfieldEntry->getFriends();

        $this->assertCount(1, $friends);
        $this->assertSame($crookshanksEntry, $friends[0]);
    }

    public function testGetIdsOfLinksArray()
    {
        $sys = new SystemProperties([
            'id' => 'cat',
            'type' => 'ContentType',
            'space' => $this->space,
            'revision' => 2,
            'createdAt' => new DateTimeImmutable('2013-06-27T22:46:12.852Z'),
            'updatedAt' => new DateTimeImmutable('2013-09-02T13:14:47.863Z'),
        ]);
        $contentType = new ConcreteContentType([
            'sys' => $sys,
            'name' => 'Cat',
            'description' => 'Meow.',
            'displayField' => 'name',
            'fields' => [
                'name' => new ConcreteField(['id' => 'name', 'name' => 'Name', 'type' => 'Text', 'localized' => true, 'disabled' => true]),
                'friends' => new ConcreteField(['id' => 'friends', 'name' => 'Friends', 'type' => 'Array', 'itemsType' => 'Link']),
            ],
        ]);

        $client = $this->createMockClient();

        $sys = new SystemProperties([
            'id' => 'garfield',
            'type' => 'Entry',
            'space' => $this->space,
            'contentType' => $contentType,
            'revision' => 56,
            'createdAt' => new DateTimeImmutable('2013-06-27T22:46:19.513Z'),
            'updatedAt' => new DateTimeImmutable('2013-09-04T09:19:39.027Z'),
        ]);
        $garfieldEntry = new ConcreteEntry(['client' => $client, 'sys' => $sys, 'fields' => [
            'name' => ['en-US' => 'Garfield'],
            'friends' => ['en-US' => [new Link('crookshanks', 'Entry'), new Link('nyancat', 'Entry')]],
        ]]);
        $garfieldEntry->setLocales($this->space->getLocales());

        $this->assertSame(['crookshanks', 'nyancat'], $garfieldEntry->getFriendsId());
    }

    /**
     * @see https://github.com/contentful/contentful.php/issues/54
     */
    public function testSingleLocale()
    {
        $sys = new SystemProperties([
            'id' => 'nyancat',
            'type' => 'Entry',
            'space' => $this->space,
            'contentType' => $this->contentType,
            'revision' => 5,
            'createdAt' => new DateTimeImmutable('2013-06-27T22:46:19.513Z'),
            'updatedAt' => new DateTimeImmutable('2013-09-04T09:19:39.027Z'),
        ]);
        $entry = new ConcreteEntry(['sys' => $sys, 'fields' => [
            'name' => ['tlh' => 'Nyan vIghro\''],
            'likes' => ['tlh' => ['rainbows', 'fish']],
            'color' => ['tlh' => 'rainbow'],
            'bestFriend' => ['tlh' => ConcreteEntry::withSys('happycat')],
            'birthday' => ['tlh' => new DateTimeImmutable('2011-04-04T22:00:00+00:00')],
            'lives' => ['tlh' => 1337],
            'image' => ['tlh' => ConcreteAsset::withSys('nyancat')],
        ]]);
        $entry->setLocales($this->space->getLocales());
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
        $this->assertSame('Nyan Cat', $this->entry->getName());
        $this->assertSame(['rainbows', 'fish'], $this->entry->getLikes());
        $this->assertSame('happycat', $this->entry->getBestFriendId());
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

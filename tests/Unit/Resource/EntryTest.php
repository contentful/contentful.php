<?php

/**
 * This file is part of the contentful/contentful package.
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
use Contentful\Delivery\Resource\Environment;
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
     * @var Environment
     */
    private $environment;

    /**
     * @var ContentType
     */
    private $contentType;

    private function createMockEnvironment()
    {
        $localeEn = new MockLocale([
            'code' => 'en-US',
            'name' => 'English (United States)',
            'fallbackCode' => null,
            'default' => true,
        ]);
        $localeTlh = new MockLocale([
            'code' => 'tlh',
            'name' => 'Klingon',
            'fallbackCode' => 'en-US',
            'default' => false,
        ]);

        return MockEnvironment::withSys('master', [
            'locales' => [$localeEn, $localeTlh],
        ]);
    }

    private function createMockContentType()
    {
        return new MockContentType([
            'sys' => new SystemProperties([
                'id' => 'cat',
                'type' => 'ContentType',
            ]),
            'name' => 'Cat',
            'description' => 'Meow.',
            'displayField' => 'name',
            'fields' => [
                'name' => new MockField('name', 'Name', 'Text', ['localized' => true, 'disabled' => true]),
                'likes' => new MockField('likes', 'Likes', 'Array', ['itemsType' => 'Symbol']),
                'color' => new MockField('color', 'Color', 'Symbol'),
                'bestFriend' => new MockField('bestFriend', 'Best Friend', 'Link', ['linkType' => 'Entry']),
                'Enemy' => new MockField('Enemy', 'Enemy', 'Link', ['linkType' => 'Entry']),
                'birthday' => new MockField('name', 'Birthday', 'Date'),
                'lifes' => new MockField('lifes', 'Lifes left', 'Integer', ['disabled' => true]),
                'lives' => new MockField('lives', 'Lives left', 'Integer'),
                'image' => new MockField('image', 'Image', 'Link', ['linkType' => 'Asset']),
            ],
        ]);
    }

    private function createMockEntry(Environment $environment, ContentType $contentType)
    {
        $sys = new SystemProperties([
            'id' => 'nyancat',
            'type' => 'Entry',
            'contentType' => $contentType,
            'environment' => $environment,
            'revision' => 5,
            'createdAt' => new DateTimeImmutable('2013-06-27T22:46:19.513Z'),
            'updatedAt' => new DateTimeImmutable('2013-09-04T09:19:39.027Z'),
        ]);

        $entry = new MockEntry(['sys' => $sys, 'fields' => [
            'name' => ['en-US' => 'Nyan Cat', 'tlh' => 'Nyan vIghro\''],
            'likes' => ['en-US' => ['rainbows', 'fish']],
            'color' => ['en-US' => 'rainbow'],
            'bestFriend' => ['en-US' => new Link('happycat', 'Entry')],
            'Enemy' => ['en-US' => new Link('garfield', 'Entry')],
            'birthday' => ['en-US' => new DateTimeImmutable('2011-04-04T22:00:00+00:00')],
            'lives' => ['en-US' => 1337],
            'lifes' => ['en-US' => 42],
            'image' => ['en-US' => new Link('nyancat', 'Asset')],
        ]]);
        $entry->initLocales($this->environment->getLocales());

        return $entry;
    }

    private function createMockClient(array $entries = [])
    {
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        foreach ($entries as $entry) {
            $entry->setClient($client);
        }

        $client->expects($this->any())
            ->method('resolveLink')
            ->willReturnCallback(function (Link $link) use ($entries) {
                foreach ($entries as $id => $entry) {
                    if ($id === $link->getId()) {
                        return $entry;
                    }
                }

                throw new NotFoundException(
                    new ClientException('Exception message', new Request('GET', ''))
                );
            });

        return $client;
    }

    public function setUp()
    {
        $this->environment = $this->createMockEnvironment();
        $this->contentType = $this->createMockContentType();
        $this->entry = $this->createMockEntry($this->environment, $this->contentType);
    }

    private function createCrookshanksEntry(ContentType $contentType)
    {
        $sys = new SystemProperties([
            'id' => 'crookshanks',
            'environment' => $this->environment,
            'contentType' => $contentType,
        ]);

        $entry = new MockEntry(['sys' => $sys, 'fields' => [
            'name' => ['en-US' => 'Crookshanks'],
        ]]);
        $entry->initLocales($this->environment->getLocales());

        return $entry;
    }

    private function createGarfieldSys(ContentType $contentType)
    {
        return new SystemProperties([
            'id' => 'garfield',
            'type' => 'Entry',
            'environment' => $this->environment,
            'contentType' => $contentType,
        ]);
    }

    public function testGetter()
    {
        $this->entry->setClient($this->createMockClient([
            'happycat' => MockEntry::withSys('happycat'),
            'garfield' => MockEntry::withSys('garfield'),
        ]));

        $this->assertSame('nyancat', $this->entry->getId());
        $sys = $this->entry->getSystemProperties();
        $this->assertSame(5, $sys->getRevision());
        $this->assertSame('2013-06-27T22:46:19.513Z', (string) $sys->getCreatedAt());
        $this->assertSame('2013-09-04T09:19:39.027Z', (string) $sys->getUpdatedAt());
        $this->assertSame($this->contentType, $this->entry->getContentType());
        $this->assertSame('happycat', $this->entry->getBestFriend()->getId());
        $this->assertSame('garfield', $this->entry->getEnemy()->getId());
        $this->assertSame('happycat', $this->entry->bestFriend->getId());
        $this->assertSame('garfield', $this->entry['enemy']->getId());

        $link = $this->entry->asLink();
        $this->assertInstanceOf(Link::class, $link);
        $this->assertSame('nyancat', $link->getId());
        $this->assertSame('Entry', $link->getLinkType());

        $this->entry->setClient(null);
    }

    public function testIdGetter()
    {
        $this->assertSame('happycat', $this->entry->getBestFriendId());
        $this->assertSame('garfield', $this->entry->getEnemyId());
        $this->assertSame('happycat', $this->entry->bestFriendId);
        $this->assertSame('garfield', $this->entry['enemyId']);
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Trying to access non existent field "invalidFieldId" on an entry with content type "Cat" ("cat").
     */
    public function testIdGetterInvalidField()
    {
        $this->entry->get('invalidFieldId');
    }

    public function testAccessingDisabledField()
    {
        $this->assertSame(42, $this->entry->getLifes());
        $this->assertSame(42, $this->entry->lifes);
        $this->assertSame(42, $this->entry['lifes']);
    }

    public function testLinkResolution()
    {
        $contentType = new MockContentType([
            'name' => 'Cat',
            'fields' => [
                'name' => new MockField('name', 'Name', 'Text', ['localized' => true, 'disabled' => true]),
                'friend' => new MockField('friend', 'Friend', 'Link'),
            ],
        ]);

        $crookshanksEntry = $this->createCrookshanksEntry($contentType);
        $garfieldEntry = new MockEntry(['sys' => $this->createGarfieldSys($contentType), 'fields' => [
            'name' => ['en-US' => 'Garfield'],
            'friend' => ['en-US' => new Link('crookshanks', 'Entry')],
        ]]);
        $garfieldEntry->initLocales($this->environment->getLocales());

        $this->createMockClient([
            'garfield' => $garfieldEntry,
            'crookshanks' => $crookshanksEntry,
        ]);

        $this->assertSame($crookshanksEntry, $garfieldEntry->getFriend());
        $this->assertLink($crookshanksEntry->getId(), 'Entry', $garfieldEntry->get('friend', null, false));
    }

    /**
     * Test for https://github.com/contentful/contentful.php/issues/9.
     */
    public function testFieldNameIncludesId()
    {
        $contentType = new MockContentType([
            'name' => 'Cat',
            'fields' => [
                'name' => new MockField('name', 'Name', 'Text', ['localized' => true, 'disabled' => true]),
                'youTubeId' => new MockField('youTubeId', 'YouTube', 'Symbol'),
            ],
        ]);

        $sys = new SystemProperties([
            'id' => 'nyancat',
            'type' => 'Entry',
            'contentType' => $contentType,
            'environment' => $this->environment,
        ]);
        $entry = new MockEntry(['sys' => $sys, 'fields' => [
            'name' => ['en-US' => 'Test Entry'],
            'youTubeId' => ['en-US' => 'l6xdPQ_O8e8'],
        ]]);
        $entry->initLocales($this->environment->getLocales());

        $this->assertSame('l6xdPQ_O8e8', $entry->getYouTubeId());
        $this->assertSame('l6xdPQ_O8e8', $entry->youtubeId);
        $this->assertSame('l6xdPQ_O8e8', $entry['youtubeId']);
    }

    public function testOneToManyReferenceWithMissingEntry()
    {
        $contentType = new MockContentType([
            'name' => 'Cat',
            'fields' => [
                'name' => new MockField('name', 'Name', 'Text', ['localized' => true, 'disabled' => true]),
                'friends' => new MockField('friends', 'Friends', 'Array', ['itemsType' => 'Link']),
            ],
        ]);

        $crookshanksEntry = $this->createCrookshanksEntry($contentType);
        $garfieldEntry = new MockEntry(['sys' => $this->createGarfieldSys($contentType), 'fields' => [
            'name' => ['en-US' => 'Garfield'],
            'friends' => ['en-US' => [new Link('crookshanks', 'Entry'), new Link('nyancat', 'Entry')]],
        ]]);
        $garfieldEntry->initLocales($this->environment->getLocales());

        $this->createMockClient([
            'garfield' => $garfieldEntry,
            'crookshanks' => $crookshanksEntry,
        ]);

        $friends = $garfieldEntry->getFriends();

        $this->assertCount(1, $friends);
        $this->assertSame($crookshanksEntry, $friends[0]);
    }

    public function testGetIdsOfLinksArray()
    {
        $contentType = new MockContentType([
            'name' => 'Cat',
            'fields' => [
                'name' => new MockField('name', 'Name', 'Text', ['localized' => true, 'disabled' => true]),
                'friends' => new MockField('friends', 'Friends', 'Array', ['itemsType' => 'Link']),
            ],
        ]);

        $garfieldEntry = new MockEntry(['sys' => $this->createGarfieldSys($contentType), 'fields' => [
            'name' => ['en-US' => 'Garfield'],
            'friends' => ['en-US' => [new Link('crookshanks', 'Entry'), new Link('nyancat', 'Entry')]],
        ]]);
        $garfieldEntry->initLocales($this->environment->getLocales());

        $this->assertSame(['crookshanks', 'nyancat'], $garfieldEntry->getFriendsId());
        $this->assertSame(['crookshanks', 'nyancat'], $garfieldEntry->friendsId);
        $this->assertSame(['crookshanks', 'nyancat'], $garfieldEntry['friendsId']);
    }

    /**
     * @see https://github.com/contentful/contentful.php/issues/54
     */
    public function testSingleLocale()
    {
        $sys = new SystemProperties([
            'id' => 'nyancat',
            'environment' => $this->environment,
            'contentType' => $this->contentType,
            'locale' => 'tlh',
        ]);
        $entry = new MockEntry(['sys' => $sys, 'fields' => [
            'likes' => ['tlh' => ['rainbows', 'fish']],
        ]]);
        $entry->initLocales($this->environment->getLocales());

        $this->assertSame(['rainbows', 'fish'], $entry->getLikes());
        $this->assertSame(['rainbows', 'fish'], $entry->likes);
        $this->assertSame(['rainbows', 'fish'], $entry['likes']);
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

    /**
     * @expectedException        \LogicException
     * @expectedExceptionMessage Entry class does not support setting fields.
     */
    public function testOffsetSetThrows()
    {
        $this->entry['fieldName'] = 'someValue';
    }

    /**
     * @expectedException        \LogicException
     * @expectedExceptionMessage Entry class does not support unsetting fields.
     */
    public function testOffsetUnsetThrows()
    {
        unset($this->entry['fieldName']);
    }

    public function testBasicMagicCalls()
    {
        $this->assertSame('Nyan Cat', $this->entry->getName());
        $this->assertSame(['rainbows', 'fish'], $this->entry->getLikes());
        $this->assertSame('happycat', $this->entry->getBestFriendId());
    }

    public function testBasicMagicCallsWithoutGet()
    {
        $this->assertSame('Nyan Cat', $this->entry->name());
        $this->assertSame(['rainbows', 'fish'], $this->entry->likes());
        $this->assertSame('happycat', $this->entry->bestFriendId());
    }

    public function testBasicGetCalls()
    {
        $this->assertSame('Nyan Cat', $this->entry->get('name'));
        $this->assertSame(['rainbows', 'fish'], $this->entry->get('likes'));
        $this->assertSame('happycat', $this->entry->get('bestFriendId'));
    }

    public function testBasicMagicGetCalls()
    {
        $this->assertSame('Nyan Cat', $this->entry->name);
        $this->assertSame(['rainbows', 'fish'], $this->entry->likes);
        $this->assertSame('happycat', $this->entry->bestFriendId);
    }

    public function testBasicOffsetCalls()
    {
        $this->assertSame('Nyan Cat', $this->entry['name']);
        $this->assertSame(['rainbows', 'fish'], $this->entry['likes']);
        $this->assertSame('happycat', $this->entry['bestFriendId']);
    }

    public function testBasicHas()
    {
        $this->assertTrue($this->entry->has('name'));
        $this->assertTrue($this->entry->has('likes'));
        $this->assertTrue($this->entry->has('bestFriend'));
        $this->assertTrue($this->entry->has('bestfriend'));
        $this->assertFalse($this->entry->has('bestFriendId'));
    }

    public function testBasicMagicHas()
    {
        $this->assertTrue($this->entry->hasName());
        $this->assertTrue($this->entry->hasLikes());
        $this->assertTrue($this->entry->hasBestFriend());
        $this->assertTrue($this->entry->hasbestfriend());
        $this->assertFalse($this->entry->hasBestFriendId());
    }

    public function testBasicMagicHasWithHasField()
    {
        $sys = new SystemProperties([
            'id' => 'crookshanks',
            'environment' => $this->environment,
            'contentType' => new MockContentType([
                'name' => 'Cat',
                'fields' => [
                    'hasLives' => new MockField('hasLives', 'Has Lives', 'Boolean'),
                    'hadBaldSpot' => new MockField('hadBaldSpot', 'Had Bald Spot', 'Boolean'),
                ],
            ]),
        ]);

        $entry = new MockEntry(['sys' => $sys, 'fields' => [
            'hasLives' => ['en-US' => false],
            'hadBaldSpot' => ['en-US' => true],
        ]]);
        $entry->initLocales($this->environment->getLocales());

        // Field is detected and returns the correct value for it
        $this->assertFalse($entry->hasLives());
        $this->assertFalse($entry->getHasLives());
        // Checks for the existence of the field
        $this->assertTrue($entry->hasHasLives());
    }

    public function testBasicIsset()
    {
        $this->assertTrue(isset($this->entry['name']));
        $this->assertTrue(isset($this->entry['likes']));
        $this->assertTrue(isset($this->entry['bestFriend']));
        $this->assertFalse(isset($this->entry['bestFriendId']));
    }

    public function testMagicGetterWithLocale()
    {
        $this->assertSame('Nyan vIghro\'', $this->entry->getName('tlh'));
        $this->assertSame('Nyan vIghro\'', $this->entry->get('name', 'tlh'));
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Trying to access the non-localized field "Likes" on content type "Cat" using the non-default locale "tlh".
     */
    public function testAccessNonLocalizedFieldWithNonDefaultLocale()
    {
        $this->entry->get('likes', 'tlh');
    }

    public function testJsonSerialize()
    {
        $this->assertJsonFixtureEqualsJsonObject('serialize.json', $this->entry);
    }
}

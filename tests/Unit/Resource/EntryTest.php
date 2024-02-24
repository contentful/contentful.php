<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Unit\Resource;

use Contentful\Core\Api\DateTimeImmutable;
use Contentful\Core\Api\Link;
use Contentful\Core\Exception\NotFoundException;
use Contentful\Core\Resource\ResourceInterface;
use Contentful\Delivery\Client\ClientInterface;
use Contentful\Delivery\Query;
use Contentful\Delivery\Resource\ContentType;
use Contentful\Delivery\Resource\Entry;
use Contentful\Delivery\Resource\Environment;
use Contentful\Delivery\Resource\Space;
use Contentful\Delivery\SystemProperties\Entry as SystemProperties;
use Contentful\Tests\Delivery\Implementation\MockClient;
use Contentful\Tests\Delivery\Implementation\MockClientEntryHas;
use Contentful\Tests\Delivery\Implementation\MockContentType;
use Contentful\Tests\Delivery\Implementation\MockEntry;
use Contentful\Tests\Delivery\Implementation\MockEnvironment;
use Contentful\Tests\Delivery\Implementation\MockField;
use Contentful\Tests\Delivery\Implementation\MockLocale;
use Contentful\Tests\Delivery\Implementation\MockSpace;
use Contentful\Tests\Delivery\TestCase;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

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
     * @var Environment
     */
    private $environment;

    /**
     * @var ContentType
     */
    private $contentType;

    private function createMockSpace(): Space
    {
        return MockSpace::withSys('spaceId');
    }

    private function createMockEnvironment(): Environment
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

    private function createMockContentType(): ContentType
    {
        return MockContentType::withSys('cat', [
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

    private function createMockEntry(): Entry
    {
        $this->space = MockSpace::withSys('spaceId');

        $sys = new SystemProperties([
            'id' => 'nyancat',
            'type' => 'Entry',
            'space' => $this->space,
            'environment' => $this->environment,
            'contentType' => $this->contentType,
            'revision' => 5,
            'createdAt' => '2013-06-27T22:46:19.513Z',
            'updatedAt' => '2013-09-04T09:19:39.027Z',
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

    private function createMockClient(array $entries = []): ClientInterface
    {
        return new class($entries) extends MockClient {
            /**
             * @var MockEntry[]
             */
            private $entries;

            public function __construct(array $entries, string $spaceId = 'spaceId', string $environmentId = 'environmentId')
            {
                $this->entries = $entries;
                foreach ($this->entries as $entry) {
                    $entry->setClient($this);
                }

                parent::__construct($spaceId, $environmentId);
            }

            public function resolveLink(Link $link, ?string $locale = null): ResourceInterface
            {
                foreach ($this->entries as $id => $entry) {
                    if ($id === $link->getId()) {
                        return $entry;
                    }
                }

                throw new NotFoundException(new ClientException('Exception message', new Request('GET', ''), new Response()));
            }

            public function resolveLinkCollection(array $links, ?string $locale = null): array
            {
                $resources = [];
                foreach ($links as $link) {
                    try {
                        $resources[] = $this->resolveLink($link, $locale);
                    } catch (NotFoundException $exception) {
                    }
                }

                return $resources;
            }
        };
    }

    protected function setUp(): void
    {
        $this->space = $this->createMockSpace();
        $this->environment = $this->createMockEnvironment();
        $this->contentType = $this->createMockContentType();
        $this->entry = $this->createMockEntry();
    }

    private function createCrookshanksEntry(ContentType $contentType)
    {
        $sys = new SystemProperties([
            'id' => 'crookshanks',
            'type' => 'Entry',
            'space' => $this->space,
            'environment' => $this->environment,
            'contentType' => $contentType,
            'revision' => 5,
            'createdAt' => '2013-06-27T22:46:19.513Z',
            'updatedAt' => '2013-09-04T09:19:39.027Z',
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
            'space' => $this->space,
            'environment' => $this->environment,
            'contentType' => $contentType,
            'revision' => 5,
            'createdAt' => '2013-06-27T22:46:19.513Z',
            'updatedAt' => '2013-09-04T09:19:39.027Z',
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

        $this->assertSame($this->space, $this->entry->getSpace());
        $this->assertSame($this->environment, $this->entry->getEnvironment());
    }

    public function testIdGetter()
    {
        $this->assertSame('happycat', $this->entry->getBestFriendId());
        $this->assertSame('garfield', $this->entry->getEnemyId());
        $this->assertSame('happycat', $this->entry->bestFriendId);
        $this->assertSame('garfield', $this->entry['enemyId']);
    }

    public function testIdGetterInvalidField()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Trying to access non existent field "invalidFieldId" on an entry with content type "Cat" ("cat").');
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
            'space' => $this->space,
            'contentType' => $contentType,
            'environment' => $this->environment,
            'revision' => 5,
            'createdAt' => '2013-06-27T22:46:19.513Z',
            'updatedAt' => '2013-09-04T09:19:39.027Z',
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
        $garfieldEntry = new MockEntry([
            'sys' => $this->createGarfieldSys($contentType),
            'fields' => [
                'name' => ['en-US' => 'Garfield'],
                'friends' => ['en-US' => [new Link('crookshanks', 'Entry'), new Link('nyancat', 'Entry')]],
            ],
        ]);
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
        $contentType = MockContentType::withSys('cat', [
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
            'type' => 'Entry',
            'space' => $this->space,
            'environment' => $this->environment,
            'contentType' => $this->contentType,
            'revision' => 5,
            'createdAt' => '2013-06-27T22:46:19.513Z',
            'updatedAt' => '2013-09-04T09:19:39.027Z',
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

    public function testNonExistingMethod()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Trying to access non existent field "noSuchMethod" on an entry with content type "Cat" ("cat").');
        $this->entry->noSuchMethod();
    }

    public function testNonExistingField()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Trying to access non existent field "NoSuchField" on an entry with content type "Cat" ("cat").');
        $this->entry->getNoSuchField();
    }

    public function testGetIdOnNonLinkField()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Trying to access non existent field "BirthdayId" on an entry with content type "Cat" ("cat").');
        $this->entry->getBirthdayId();
    }

    public function testOffsetSetThrows()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Entry class does not support setting fields.');
        $this->entry['fieldName'] = 'someValue';
    }

    public function testOffsetUnsetThrows()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Entry class does not support unsetting fields.');
        unset($this->entry['fieldName']);
    }

    public function testGet()
    {
        $this->assertSame('Nyan Cat', $this->entry->getName());
        $this->assertSame('Nyan Cat', $this->entry->name());
        $this->assertSame('Nyan Cat', $this->entry->get('name'));
        $this->assertSame('Nyan Cat', $this->entry->name);
        $this->assertSame('Nyan Cat', $this->entry['name']);

        $this->assertSame(['rainbows', 'fish'], $this->entry->getLikes());
        $this->assertSame(['rainbows', 'fish'], $this->entry->likes());
        $this->assertSame(['rainbows', 'fish'], $this->entry->get('likes'));
        $this->assertSame(['rainbows', 'fish'], $this->entry->likes);
        $this->assertSame(['rainbows', 'fish'], $this->entry['likes']);

        $this->assertSame('happycat', $this->entry->getBestFriendId());
        $this->assertSame('happycat', $this->entry->bestFriendId());
        $this->assertSame('happycat', $this->entry->get('bestFriendId'));
        $this->assertSame('happycat', $this->entry->bestFriendId);
        $this->assertSame('happycat', $this->entry['bestFriendId']);
    }

    public function testHas()
    {
        $client = $this->entry->getClient();
        $this->entry->setCLient(new MockClientEntryHas(['happycat']));

        $this->assertTrue($this->entry->has('name'));
        $this->assertTrue($this->entry->hasName());

        $this->assertTrue($this->entry->has('likes'));
        $this->assertTrue($this->entry->hasLikes());

        $this->assertTrue($this->entry->has('bestFriend'));
        $this->assertTrue($this->entry->hasBestFriend());

        $this->assertTrue($this->entry->has('bestfriend'));
        $this->assertTrue($this->entry->hasbestfriend());

        $this->assertFalse($this->entry->has('bestFriendId'));
        $this->assertFalse($this->entry->hasBestFriendId());

        $this->assertFalse($this->entry->has('image'));
        $this->assertFalse($this->entry->hasImage());

        $this->entry->setClient($client);
    }

    public function testBasicMagicHasWithHasField()
    {
        $sys = new SystemProperties([
            'id' => 'crookshanks',
            'type' => 'Entry',
            'space' => $this->space,
            'environment' => $this->environment,
            'contentType' => MockContentType::withSys('cat', [
                'name' => 'Cat',
                'fields' => [
                    'hasLives' => new MockField('hasLives', 'Has Lives', 'Boolean'),
                    'hadBaldSpot' => new MockField('hadBaldSpot', 'Had Bald Spot', 'Boolean'),
                ],
            ]),
            'revision' => 5,
            'createdAt' => '2013-06-27T22:46:19.513Z',
            'updatedAt' => '2013-09-04T09:19:39.027Z',
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
        $client = $this->entry->getClient();
        $this->entry->setCLient(new MockClientEntryHas(['happycat']));

        $this->assertTrue(isset($this->entry['name']));
        $this->assertTrue(isset($this->entry['likes']));
        $this->assertTrue(isset($this->entry['bestFriend']));
        $this->assertFalse(isset($this->entry['bestFriendId']));
        $this->assertFalse(isset($this->entry['image']));

        $this->assertTrue(isset($this->entry->name));
        $this->assertTrue(isset($this->entry->likes));
        $this->assertTrue(isset($this->entry->bestFriend));
        $this->assertFalse(isset($this->entry->bestFriendId));
        $this->assertFalse(isset($this->entry->image));

        $this->entry->setClient($client);
    }

    public function testMagicGetterWithLocale()
    {
        $this->assertSame('Nyan vIghro\'', $this->entry->getName('tlh'));
        $this->assertSame('Nyan vIghro\'', $this->entry->get('name', 'tlh'));
    }

    public function testAccessNonLocalizedFieldWithNonDefaultLocale()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Trying to access the non-localized field "Likes" on content type "Cat" using the non-default locale "tlh".');
        $this->entry->get('likes', 'tlh');
    }

    public function testLinksToEntry()
    {
        $entry = MockEntry::withSys('entryId');
        $client = new MockClient();

        $entry->setClient($client);

        // Result will be a dummy, so we inspect the actual query
        $entry->getReferences();
        $query = $client->getLastQuery();
        $this->assertSame('links_to_entry=entryId', $query->getQueryString());

        $query = (new Query())
            ->setContentType('someContentType')
        ;
        $entry->getReferences($query);
        $query = $client->getLastQuery();
        $this->assertSame('content_type=someContentType&links_to_entry=entryId', $query->getQueryString());
    }

    public function testFallbackChain()
    {
        $environment = MockEnvironment::withSys('environmentId', [
            'locales' => [
                new MockLocale([
                    'code' => 'en-US',
                    'name' => 'English (United States)',
                    'fallbackCode' => null,
                    'default' => true,
                ]),
                new MockLocale([
                    'code' => 'tlh',
                    'name' => 'Klingon',
                    'fallbackCode' => 'en-US',
                    'default' => false,
                ]),
                new MockLocale([
                    'code' => 'it-IT',
                    'name' => 'Italian',
                    'fallbackCode' => null,
                    'default' => false,
                ]),
            ],
        ]);

        $sys = new SystemProperties([
            'id' => 'entryId',
            'type' => 'Entry',
            'space' => $this->space,
            'environment' => $environment,
            'contentType' => MockContentType::withSys('person', [
                'name' => 'Person',
                'displayField' => 'name',
                'fields' => [
                    'field1' => new MockField('field1', 'Field 1', 'Text', ['localized' => true]),
                    'field2' => new MockField('field2', 'Field 2', 'Array', ['localized' => true, 'itemsType' => 'Symbol']),
                    'field3' => new MockField('field3', 'Field 3', 'Text', ['localized' => true, 'itemsType' => 'Symbol']),
                    'field4' => new MockField('field4', 'Field 4', 'Array', ['localized' => true, 'itemsType' => 'Symbol']),
                    'field5' => new MockField('field5', 'Field 5', 'Text', ['localized' => true, 'itemsType' => 'Symbol']),
                ],
            ]),
            'revision' => 1,
            'createdAt' => '2018-01-01T12:00:00.123Z',
            'updatedAt' => '2018-01-01T12:00:00.123Z',
        ]);

        $entry = new MockEntry([
            'sys' => $sys,
            'fields' => [
                'field1' => [
                    'en-US' => 'Some value',
                ],
                'field2' => [
                    'en-US' => 'Another value',
                ],
                'field3' => [
                    'en-US' => 'More values',
                ],
            ],
        ]);
        $entry->initLocales($environment->getLocales());

        $this->assertSame('Some value', $entry->get('field1', 'tlh'));
        $this->assertSame([], $entry->get('field2', 'it-IT'));
        $this->assertNull($entry->get('field3', 'it-IT'));
        $this->assertSame([], $entry->get('field4', 'en-US'));
        $this->assertNull($entry->get('field5', 'en-US'));
    }

    public function testJsonSerialize()
    {
        $this->assertJsonFixtureEqualsJsonObject('serialize.json', $this->entry);
    }
}

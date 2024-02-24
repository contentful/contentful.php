<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Unit\Mapper;

use Contentful\Core\Api\DateTimeImmutable;
use Contentful\Core\Api\Link;
use Contentful\Core\Api\Location;
use Contentful\Core\ResourceBuilder\ObjectHydrator;
use Contentful\Delivery\Mapper\Entry as Mapper;
use Contentful\Delivery\Resource\ContentType;
use Contentful\Delivery\Resource\ContentType\Field;
use Contentful\Delivery\Resource\Entry;
use Contentful\RichText\Node\NodeInterface;
use Contentful\RichText\Node\Text;
use Contentful\Tests\Delivery\Implementation\MockClient;
use Contentful\Tests\Delivery\Implementation\MockContentType;
use Contentful\Tests\Delivery\Implementation\MockEntry;
use Contentful\Tests\Delivery\Implementation\MockEnvironment;
use Contentful\Tests\Delivery\Implementation\MockParser;
use Contentful\Tests\Delivery\Implementation\MockResourceBuilder;
use Contentful\Tests\Delivery\Implementation\MockSpace;
use Contentful\Tests\Delivery\TestCase;

class EntryTest extends TestCase
{
    private function createContentType(): ContentType
    {
        $fields = [
            'name' => new Field('name', 'Name', 'Symbol'),
            'description' => new Field('description', 'Description', 'Text'),
            'age' => new Field('age', 'Age', 'Integer'),
            'favoriteNumber' => new Field('favoriteNumber', 'Favorite number', 'Number'),
            'city' => new Field('city', 'City', 'Location'),
            'birthday' => new Field('birthday', 'Birthday', 'Date'),
            'isActive' => new Field('isActive', 'Is active', 'Boolean'),
            'bestFriend' => new Field('bestFriend', 'Best friend', 'Link'),
            'pictures' => new Field('pictures', 'Pictures', 'Array'),
            'custom' => new Field('custom', 'Custom', 'Object'),
            'nullValue' => new Field('nullValue', 'Null value', 'Symbol'),
            'richText' => new Field('richText', 'Rich text', 'RichText'),
        ];

        $hydrator = new ObjectHydrator();
        $hydrator->hydrate($fields['bestFriend'], [
            'linkType' => 'Entry',
        ]);
        $hydrator->hydrate($fields['pictures'], [
            'itemsType' => 'Link',
            'itemsLinkType' => 'Asset',
        ]);

        return MockContentType::withSys('person', [
            'name' => 'Person',
            'displayField' => 'name',
            'description' => 'A real person',
            'fields' => $fields,
        ]);
    }

    public function testMapper()
    {
        $mapper = new Mapper(
            new MockResourceBuilder(),
            new MockClient(),
            new MockParser()
        );

        $space = MockSpace::withSys('spaceId');
        $environment = MockEnvironment::withSys('environmentId');
        $contentType = $this->createContentType();

        $hasTriggeredError = false;
        set_error_handler(function (int $errorNumber, string $message) use (&$hasTriggeredError) {
            // I'm not clear why we're checking the INI here, but this is probably not needed right now
            // $this->assertSame(0, \error_reporting(\E_USER_DEPRECATED));
            $this->assertSame('Entry of content type "Person" ("person") being built contains field "extraField" which is not present in the content type definition. Please check your cache for stale content type definitions.', $message);
            $hasTriggeredError = true;
        }, \E_ALL);

        /** @var Entry $resource */
        $resource = $mapper->map(null, [
            'sys' => [
                'id' => 'saitama',
                'type' => 'Entry',
                'space' => $space,
                'environment' => $environment,
                'contentType' => $contentType,
                'revision' => 1,
                'createdAt' => '2016-01-01T12:00:00.123Z',
                'updatedAt' => '2017-01-01T12:00:00.123Z',
                'locale' => 'en-US',
            ],
            'fields' => [
                'name' => 'Saitama',
                'description' => 'One-Punch Man',
                'age' => 25,
                'favoriteNumber' => 1.0,
                'city' => [
                    'lat' => 37.6452283,
                    'lon' => 138.7669125,
                ],
                'birthday' => '2012-12-12T12:00:00.123Z',
                'isActive' => true,
                'bestFriend' => [
                    'sys' => [
                        'type' => 'Link',
                        'linkType' => 'Entry',
                        'id' => 'genos',
                    ],
                ],
                'pictures' => [
                    [
                        'sys' => [
                            'type' => 'Link',
                            'linkType' => 'Asset',
                            'id' => 'saitama',
                        ],
                    ],
                    new Link('saitama2', 'Asset'),
                ],
                'custom' => [
                    'rank' => 'B',
                ],
                'nullValue' => null,
                'extraField' => 'Some extra field',
                'richText' => [
                    'nodeType' => 'document',
                    'content' => [],
                ],
            ],
        ]);

        $this->assertTrue($hasTriggeredError);

        $this->assertInstanceOf(Entry::class, $resource);
        $this->assertSame('saitama', $resource->getId());
        $this->assertSame('Entry', $resource->getType());

        $sys = $resource->getSystemProperties();
        $this->assertSame($space, $sys->getSpace());
        $this->assertSame($environment, $sys->getEnvironment());
        $this->assertSame($contentType, $sys->getContentType());
        $this->assertSame(1, $sys->getRevision());
        $this->assertSame('2016-01-01T12:00:00.123Z', (string) $sys->getCreatedAt());
        $this->assertSame('2017-01-01T12:00:00.123Z', (string) $sys->getUpdatedAt());

        $this->assertIsString($resource->get('name'));
        $this->assertSame('Saitama', $resource->get('name'));

        $this->assertIsString($resource->get('description'));
        $this->assertSame('One-Punch Man', $resource->get('description'));

        $this->assertIsInt($resource->get('age'));
        $this->assertSame(25, $resource->get('age'));

        $this->assertIsFloat($resource->get('favoriteNumber'));
        $this->assertSame(1.0, $resource->get('favoriteNumber'));

        /** @var Location $city */
        $city = $resource->get('city');
        $this->assertInstanceOf(Location::class, $city);
        $this->assertSame(37.6452283, $city->getLatitude());
        $this->assertSame(138.7669125, $city->getLongitude());

        $this->assertInstanceOf(DateTimeImmutable::class, $resource->get('birthday'));
        $this->assertSame('2012-12-12T12:00:00.123Z', (string) $resource->get('birthday'));

        $this->assertIsBool($resource->get('isActive'));
        $this->assertTrue($resource->get('isActive'));

        $this->assertInstanceOf(Link::class, $resource->get('bestFriend', null, false));
        $this->assertLink('genos', 'Entry', $resource->get('bestFriend', null, false));

        $this->assertContainsOnlyInstancesOf(Link::class, $resource->get('pictures', null, false));
        $this->assertLink('saitama', 'Asset', $resource->get('pictures', null, false)[0]);
        $this->assertLink('saitama2', 'Asset', $resource->get('pictures', null, false)[1]);

        $this->assertNull($resource->get('nullValue'));

        $this->assertIsString($resource->get('extraField'));
        $this->assertSame('Some extra field', $resource->get('extraField'));

        $this->assertInstanceOf(NodeInterface::class, $resource->get('richText'));
        $this->assertInstanceOf(Text::class, $resource->get('richText'));
    }

    public function testWithPreviousEntry()
    {
        $mapper = new Mapper(
            new MockResourceBuilder(),
            new MockClient(),
            new MockParser()
        );

        $space = MockSpace::withSys('spaceId');
        $environment = MockEnvironment::withSys('environmentId');

        $entry = MockEntry::withSys();
        $contentType = $entry->getContentType();
        $contentType->addUnknownField('name');
        $contentType->addUnknownField('age');

        /** @var Entry $entry */
        $entry = $mapper->map($entry, [
            'sys' => [
                'id' => 'saitama',
                'type' => 'Entry',
                'space' => $space,
                'environment' => $environment,
                'contentType' => $contentType,
                'revision' => 1,
                'createdAt' => '2016-01-01T12:00:00.123Z',
                'updatedAt' => '2017-01-01T12:00:00.123Z',
                'locale' => 'en-US',
            ],
            'fields' => [
                'name' => 'Saitama',
            ],
        ]);

        $this->assertSame('Saitama', $entry->get('name'));
        $this->assertNull($entry->get('age'));

        $entry = $mapper->map($entry, [
            'sys' => [
                'id' => 'saitama',
                'type' => 'Entry',
                'space' => $space,
                'environment' => $environment,
                'contentType' => $contentType,
                'revision' => 1,
                'createdAt' => '2016-01-01T12:00:00.123Z',
                'updatedAt' => '2017-01-01T12:00:00.123Z',
                'locale' => 'en-US',
            ],
            'fields' => [
                'age' => 25,
            ],
        ]);

        $this->assertSame('Saitama', $entry->get('name'));
        $this->assertSame(25, $entry->get('age'));
    }
}

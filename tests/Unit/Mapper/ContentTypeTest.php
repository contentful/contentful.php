<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Unit\Mapper;

use Contentful\Delivery\Mapper\ContentType as Mapper;
use Contentful\Delivery\Resource\ContentType;
use Contentful\Delivery\Resource\ContentType\Field;
use Contentful\Tests\Delivery\Implementation\MockClient;
use Contentful\Tests\Delivery\Implementation\MockEnvironment;
use Contentful\Tests\Delivery\Implementation\MockParser;
use Contentful\Tests\Delivery\Implementation\MockResourceBuilder;
use Contentful\Tests\Delivery\Implementation\MockSpace;
use Contentful\Tests\Delivery\TestCase;

class ContentTypeTest extends TestCase
{
    public function testMapper()
    {
        $mapper = new Mapper(
            new MockResourceBuilder(),
            new MockClient(),
            new MockParser()
        );

        $space = MockSpace::withSys('spaceId');
        $environment = MockEnvironment::withSys('environmentId');

        $fields = [
            'name' => [
                'id' => 'name',
                'name' => 'Name',
                'type' => 'Symbol',
                'required' => false,
                'localized' => false,
            ],
            'description' => [
                'id' => 'description',
                'name' => 'Description',
                'type' => 'Text',
                'required' => false,
                'localized' => false,
            ],
            'age' => [
                'id' => 'age',
                'name' => 'Age',
                'type' => 'Integer',
                'required' => false,
                'localized' => false,
            ],
            'favoriteNumber' => [
                'id' => 'favoriteNumber',
                'name' => 'Favorite number',
                'type' => 'Number',
                'required' => false,
                'localized' => false,
            ],
            'city' => [
                'id' => 'city',
                'name' => 'City',
                'type' => 'Location',
                'required' => false,
                'localized' => false,
            ],
            'birthday' => [
                'id' => 'birthday',
                'name' => 'Birthday',
                'type' => 'Date',
                'required' => true,
                'localized' => true,
            ],
            'isActive' => [
                'id' => 'isActive',
                'name' => 'Is active',
                'type' => 'Boolean',
                'required' => false,
                'localized' => false,
                'disabled' => true,
            ],
            'bestFriend' => [
                'id' => 'bestFriend',
                'name' => 'Best friend',
                'type' => 'Link',
                'required' => false,
                'localized' => false,
                'linkType' => 'Entry',
            ],
            'pictures' => [
                'id' => 'pictures',
                'name' => 'Pictures',
                'type' => 'Array',
                'required' => false,
                'localized' => false,
                'items' => [
                    'type' => 'Link',
                    'linkType' => 'Asset',
                ],
            ],
            'custom' => [
                'id' => 'custom',
                'name' => 'Custom',
                'type' => 'Object',
                'required' => false,
                'localized' => false,
            ],
        ];
        /** @var ContentType $resource */
        $resource = $mapper->map(null, [
            'sys' => [
                'id' => 'contentTypeId',
                'type' => 'ContentType',
                'space' => $space,
                'environment' => $environment,
                'revision' => 1,
                'createdAt' => '2016-01-01T12:00:00.123Z',
                'updatedAt' => '2017-01-01T12:00:00.123Z',
                'deletedAt' => '2018-01-01T12:00:00.123Z',
            ],
            'name' => 'Person',
            'displayField' => 'name',
            'description' => 'A real person',
            'fields' => $fields,
        ]);

        $this->assertInstanceOf(ContentType::class, $resource);
        $this->assertSame('contentTypeId', $resource->getId());
        $this->assertSame('ContentType', $resource->getType());

        $sys = $resource->getSystemProperties();
        $this->assertSame($space, $sys->getSpace());
        $this->assertSame($environment, $sys->getEnvironment());
        $this->assertSame(1, $sys->getRevision());
        $this->assertSame('2016-01-01T12:00:00.123Z', (string) $sys->getCreatedAt());
        $this->assertSame('2017-01-01T12:00:00.123Z', (string) $sys->getUpdatedAt());

        foreach ($resource->getFields() as $fieldId => $field) {
            $this->assertField($field, $fields[$fieldId]);
        }
    }

    private function assertField(Field $field, array $values)
    {
        $this->assertSame($values['id'], $field->getId());
        $this->assertSame($values['name'], $field->getName());
        $this->assertSame($values['type'], $field->getType());
        $this->assertSame($values['required'] ?? false, $field->isRequired());
        $this->assertSame($values['localized'] ?? false, $field->isLocalized());
        $this->assertSame($values['disabled'] ?? false, $field->isDisabled());
        $this->assertSame($values['linkType'] ?? null, $field->getLinkType());
        $this->assertSame($values['items']['type'] ?? null, $field->getItemsType());
        $this->assertSame($values['items']['linkType'] ?? null, $field->getItemsLinkType());
    }
}

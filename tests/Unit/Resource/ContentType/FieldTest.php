<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Unit\Resource\ContentType;

use Contentful\Tests\Delivery\Implementation\MockField;
use Contentful\Tests\Delivery\TestCase;

class FieldTest extends TestCase
{
    public function testGetter()
    {
        $field = new MockField('id', 'name', 'type', [
            'linkType' => 'linkType',
            'itemsType' => 'itemsType',
            'itemsLinkType' => 'itemsLinkType',
            'required' => true,
            'localized' => false,
            'disabled' => false,
        ]);

        $this->assertSame('id', $field->getId());
        $this->assertSame('name', $field->getName());
        $this->assertSame('type', $field->getType());
        $this->assertSame('linkType', $field->getLinkType());
        $this->assertSame('itemsLinkType', $field->getItemsLinkType());
        $this->assertSame('itemsType', $field->getItemsType());
        $this->assertTrue($field->isRequired());
        $this->assertFalse($field->isLocalized());
        $this->assertFalse($field->isDisabled());
    }

    public function testJsonSerialize()
    {
        $field1 = new MockField('one', 'oneField', 'Link', [
            'linkType' => 'Asset',
            'itemsType' => null,
            'itemsLinkType' => null,
            'required' => true,
            'localized' => true,
            'disabled' => true,
        ]);
        $this->assertJsonFixtureEqualsJsonObject('serialize_one.json', $field1);

        $field2 = new MockField('many', 'manyField', 'Array', [
            'linkType' => null,
            'itemsType' => 'Link',
            'itemsLinkType' => 'Asset',
            'required' => false,
            'localized' => true,
            'disabled' => false,
        ]);
        $this->assertJsonFixtureEqualsJsonObject('serialize_many.json', $field2);
    }
}

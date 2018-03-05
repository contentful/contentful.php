<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery\Unit\Resource\ContentType;

use Contentful\Delivery\Resource\ContentType\Field;
use Contentful\Tests\Delivery\TestCase;

class FieldTest extends TestCase
{
    public function testGetter()
    {
        $field = new Field(
            'id',
            'name',
            'type',
            'linkType',
            'itemsType',
            'itemsLinkType',
            true,
            false,
            false
        );

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
        $field1 = new Field(
            'one',
            'oneField',
            'Link',
            'Asset',
            null,
            null,
            true,
            true,
            true
        );

        $this->assertJsonFixtureEqualsJsonObject('serialize_one.json', $field1);

        $field2 = new Field(
            'many',
            'manyField',
            'Array',
            null,
            'Link',
            'Asset',
            false,
            true,
            false
        );

        $this->assertJsonFixtureEqualsJsonObject('serialize_many.json', $field2);
    }
}

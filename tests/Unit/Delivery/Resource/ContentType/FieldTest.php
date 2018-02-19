<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\Delivery\Resource\ContentType;

use Contentful\Delivery\Resource\ContentType\Field;

class FieldTest extends \PHPUnit_Framework_TestCase
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

        $this->assertJsonStringEqualsJsonString('{"name":"oneField","id":"one","type":"Link","required":true,"localized":true,"disabled":true,"linkType":"Asset"}', \json_encode($field1));

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

        $this->assertJsonStringEqualsJsonString('{"name":"manyField","id":"many","type":"Array","required":false,"localized":true,"items":{"type":"Link","linkType":"Asset"}}', \json_encode($field2));
    }
}

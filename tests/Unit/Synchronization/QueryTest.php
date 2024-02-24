<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Unit\Synchronization;

use Contentful\Delivery\Resource\ContentType;
use Contentful\Delivery\Synchronization\Query;
use Contentful\Tests\Delivery\TestCase;

class QueryTest extends TestCase
{
    public function testFilterWithNoOptions()
    {
        $query = new Query();

        $this->assertSame('initial=true', $query->getQueryString());
    }

    public function testSetTypeInvalidValue()
    {
        $this->expectException(\InvalidArgumentException::class);

        (new Query())
            ->setType('Invalid')
        ;
    }

    public function testFilterByType()
    {
        $query = (new Query())
            ->setType('Entry')
        ;

        $this->assertSame('initial=true&type=Entry', $query->getQueryString());
    }

    public function testGetSetContentTypeFromObject()
    {
        $query = (new Query())
            ->setContentType(new class() extends ContentType {
                public function __construct()
                {
                }

                public function getId(): string
                {
                    return 'cat';
                }
            })
        ;

        $this->assertSame('initial=true&type=Entry&content_type=cat', $query->getQueryString());
    }

    public function testFilterByContentType()
    {
        $query = (new Query())
            ->setContentType('cat')
        ;

        $this->assertSame('initial=true&type=Entry&content_type=cat', $query->getQueryString());
    }
}

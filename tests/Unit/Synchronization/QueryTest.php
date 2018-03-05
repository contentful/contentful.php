<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery\Unit\Synchronization;

use Contentful\Delivery\Resource\ContentType;
use Contentful\Delivery\Synchronization\Query;
use Contentful\Tests\Delivery\TestCase;

class QueryTest extends TestCase
{
    public function testFilterWithNoOptions()
    {
        $queryBuilder = new Query();

        $this->assertSame('initial=1', $queryBuilder->getQueryString());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetTypeInvalidValue()
    {
        $queryBuilder = new Query();
        $queryBuilder->setType('Invalid');
    }

    public function testFilterByType()
    {
        $queryBuilder = new Query();
        $queryBuilder->setType('Entry');

        $this->assertSame('initial=1&type=Entry', $queryBuilder->getQueryString());
    }

    public function testGetSetContentTypeFromObject()
    {
        $queryBuilder = new Query();
        $contentType = $this->getMockBuilder(ContentType::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contentType->method('getId')
            ->willReturn('cat');

        $queryBuilder->setContentType($contentType);

        $this->assertSame('initial=1&type=Entry&content_type=cat', $queryBuilder->getQueryString());
    }

    public function testFilterByContentType()
    {
        $queryBuilder = new Query();
        $queryBuilder->setContentType('cat');

        $this->assertSame('initial=1&type=Entry&content_type=cat', $queryBuilder->getQueryString());
    }
}

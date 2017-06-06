<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\Delivery\Synchronization;

use Contentful\Delivery\ContentType;
use Contentful\Delivery\Synchronization\Query;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    public function testFilterWithNoOptions()
    {
        $queryBuilder = new Query;

        $this->assertEquals('initial=1', $queryBuilder->getQueryString());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetTypeInvalidValue()
    {
        $queryBuilder = new Query;
        $queryBuilder->setType('Invalid');
    }

    /**
     * @uses Contentful\Delivery\Synchronization\Query::setType
     */
    public function testFilterByType()
    {
        $queryBuilder = new Query;
        $queryBuilder->setType('Entry');

        $this->assertEquals('initial=1&type=Entry', $queryBuilder->getQueryString());
    }

    /**
     * @uses Contentful\Delivery\Synchronization\Query::getQueryData
     * @uses Contentful\Delivery\Synchronization\Query::setType
     */
    public function testGetSetContentTypeFromObject()
    {
        $queryBuilder = new Query;
        $contentType = $this->getMockBuilder(ContentType::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contentType->method('getId')
            ->willReturn('cat');

        $queryBuilder->setContentType($contentType);

        $this->assertEquals('initial=1&type=Entry&content_type=cat', $queryBuilder->getQueryString());
    }

    /**
     * @uses Contentful\Delivery\Synchronization\Query::setType
     */
    public function testFilterByContentType()
    {
        $queryBuilder = new Query;
        $queryBuilder->setContentType('cat');

        $this->assertEquals('initial=1&type=Entry&content_type=cat', $queryBuilder->getQueryString());
    }
}

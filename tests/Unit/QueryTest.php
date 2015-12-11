<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit;

use Contentful\Query;
use Contentful\Location;
use Contentful\Delivery\ContentType;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Contentful\Query::__construct
     * @covers Contentful\Query::getQueryData
     * @covers Contentful\Query::getQueryString
     */
    public function testFilterWithNoOptions()
    {
        $queryBuilder = new Query;

        $this->assertEquals('', $queryBuilder->getQueryString());
    }

    /**
     * @covers Contentful\Query::__construct
     * @covers Contentful\Query::setLimit
     * @covers Contentful\Query::getLimit
     */
    public function testGetSetLimit()
    {
        $queryBuilder = new Query;
        $queryBuilder->setLimit(150);

        $this->assertEquals(150, $queryBuilder->getLimit());
    }

    /**
     * @covers Contentful\Query::__construct
     * @covers Contentful\Query::setLimit
     * @covers Contentful\Query::getQueryData
     * @covers Contentful\Query::getQueryString
     */
    public function testFilterWithLimit()
    {
        $queryBuilder = new Query;
        $queryBuilder->setLimit(150);

        $this->assertEquals('limit=150', $queryBuilder->getQueryString());
    }

    /**
     * @covers Contentful\Query::__construct
     * @covers Contentful\Query::setLimit
     *
     * @expectedException \RangeException
     */
    public function testLimitThrowsOnValueTooLarge()
    {
        $queryBuilder = new Query;
        $queryBuilder->setLimit(1500);
    }

    /**
     * @covers Contentful\Query::__construct
     * @covers Contentful\Query::setLimit
     *
     * @expectedException \RangeException
     */
    public function testLimitThrowsOnValueZero()
    {
        $queryBuilder = new Query;
        $queryBuilder->setLimit(0);
    }

    /**
     * @covers Contentful\Query::__construct
     * @covers Contentful\Query::setLimit
     *
     * @expectedException \RangeException
     */
    public function testLimitThrowsOnValueNegative()
    {
        $queryBuilder = new Query;
        $queryBuilder->setLimit(-1);
    }

    /**
     * @covers Contentful\Query::__construct
     * @covers Contentful\Query::setSkip
     * @covers Contentful\Query::getSkip
     */
    public function testGetSetSkip()
    {
        $queryBuilder = new Query;
        $queryBuilder->setSkip(10);

        $this->assertEquals(10, $queryBuilder->getSkip());
    }

    /**
     * @covers Contentful\Query::__construct
     * @covers Contentful\Query::setSkip
     * @covers Contentful\Query::getQueryData
     * @covers Contentful\Query::getQueryString
     */
    public function testFilterWithSkip()
    {
        $queryBuilder = new Query;
        $queryBuilder->setSkip(10);

        $this->assertEquals('skip=10', $queryBuilder->getQueryString());
    }

    /**
     * @covers Contentful\Query::__construct
     * @covers Contentful\Query::setSkip
     *
     * @expectedException \RangeException
     */
    public function testSkipThrowsOnValueNegative()
    {
        $queryBuilder = new Query;
        $queryBuilder->setSkip(-1);
    }

    /**
     * @covers Contentful\Query::__construct
     * @covers Contentful\Query::orderBy
     * @covers Contentful\Query::getQueryData
     * @covers Contentful\Query::getQueryString
     */
    public function testFilterOrderBy()
    {
        $queryBuilder = new Query;
        $queryBuilder->orderBy('sys.createdAt');

        $this->assertEquals('order=sys.createdAt', $queryBuilder->getQueryString());
    }

    /**
     * @covers Contentful\Query::__construct
     * @covers Contentful\Query::orderBy
     * @covers Contentful\Query::getQueryData
     * @covers Contentful\Query::getQueryString
     */
    public function testFilterOrderByReversed()
    {
        $queryBuilder = new Query;
        $queryBuilder->orderBy('sys.createdAt', true);

        $this->assertEquals('order=-sys.createdAt', $queryBuilder->getQueryString());
    }

    /**
     * @covers Contentful\Query::__construct
     * @covers Contentful\Query::setContentType
     * @covers Contentful\Query::getContentType
     */
    public function testGetSetContentType()
    {
        $queryBuilder = new Query;
        $queryBuilder->setContentType('cat');

        $this->assertEquals('cat', $queryBuilder->getContentType());
    }

    /**
     * @covers Contentful\Query::__construct
     * @covers Contentful\Query::setContentType
     * @covers Contentful\Query::getContentType
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

        $this->assertEquals('cat', $queryBuilder->getContentType());
    }

    /**
     * @covers Contentful\Query::__construct
     * @covers Contentful\Query::setContentType
     * @covers Contentful\Query::getQueryData
     * @covers Contentful\Query::getQueryString
     */
    public function testFilterByContentType()
    {
        $queryBuilder = new Query;
        $queryBuilder->setContentType('cat');

        $this->assertEquals('content_type=cat', $queryBuilder->getQueryString());
    }

    /**
     * @covers Contentful\Query::__construct
     * @covers Contentful\Query::where
     * @covers Contentful\Query::getQueryData
     * @covers Contentful\Query::getQueryString
     */
    public function testWhere()
    {
        $queryBuilder = new Query;
        $queryBuilder->where('sys.id', 'nyancat');

        $this->assertEquals('sys.id=nyancat', $queryBuilder->getQueryString());
    }

    /**
     * @covers Contentful\Query::__construct
     * @covers Contentful\Query::where
     * @covers Contentful\Query::getQueryData
     * @covers Contentful\Query::getQueryString
     */
    public function testWhereOperator()
    {
        $queryBuilder = new Query;
        $queryBuilder->where('sys.id', 'nyancat', 'ne');

        $this->assertEquals('sys.id%5Bne%5D=nyancat', $queryBuilder->getQueryString());
    }

    /**
     * @covers Contentful\Query::__construct
     * @covers Contentful\Query::where
     * @covers Contentful\Query::getQueryData
     * @covers Contentful\Query::getQueryString
     */
    public function testWhereDateTime()
    {
        $queryBuilder = new Query;
        $queryBuilder->where('sys.updatedAt', new \DateTimeImmutable('2013-01-01T00:00:00Z'), 'lte');

        $this->assertEquals('sys.updatedAt%5Blte%5D=2013-01-01T00%3A00%3A00%2B00%3A00', $queryBuilder->getQueryString());
    }

    /**
     * @covers Contentful\Query::__construct
     * @covers Contentful\Query::where
     * @covers Contentful\Query::getQueryData
     * @covers Contentful\Query::getQueryString
     */
    public function testWhereDateTimeResetsSeconds()
    {
        $queryBuilder = new Query;
        $queryBuilder->where('sys.updatedAt', new \DateTimeImmutable('2013-01-01T12:30:35Z'), 'lte');

        $this->assertEquals('sys.updatedAt%5Blte%5D=2013-01-01T12%3A30%3A00%2B00%3A00', $queryBuilder->getQueryString());
    }

    /**
     * @covers Contentful\Query::__construct
     * @covers Contentful\Query::where
     * @covers Contentful\Query::getQueryData
     * @covers Contentful\Query::getQueryString
     *
     * @uses Contentful\Location::__construct
     * @uses Contentful\Location::queryStringFormatted
     */
    public function testWhereLocation()
    {
        $queryBuilder = new Query;
        $queryBuilder->where('fields.center', new Location(15.0, 17.8), 'near');

        $this->assertEquals('fields.center%5Bnear%5D=15%2C17.8', $queryBuilder->getQueryString());
    }

    /**
     * @covers Contentful\Query::__construct
     * @covers Contentful\Query::where
     *
     * @expectedException \InvalidArgumentException
     */
    public function testWhereUnknownOperator()
    {
        $queryBuilder = new Query;
        $queryBuilder->where('sys.id', 'nyancat', 'wrong');
    }

    /**
     * @covers Contentful\Query::__construct
     * @covers Contentful\Query::setMimeTypeGroup
     * @covers Contentful\Query::getMimeTypeGroup
     */
    public function testGetSetMimeTypeGroup()
    {
        $queryBuilder = new Query;
        $queryBuilder->setMimeTypeGroup('image');

        $this->assertEquals('image', $queryBuilder->getMimeTypeGroup());
    }

    /**
     * @covers Contentful\Query::__construct
     * @covers Contentful\Query::setMimeTypeGroup
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSetMimeTypeGroupInvalid()
    {
        $queryBuilder = new Query;
        $queryBuilder->setMimeTypeGroup('invalid');
    }

    /**
     * @covers Contentful\Query::__construct
     * @covers Contentful\Query::setMimeTypeGroup
     * @covers Contentful\Query::getQueryData
     * @covers Contentful\Query::getQueryString
     */
    public function testFilterByMimeTypeGroup()
    {
        $queryBuilder = new Query;
        $queryBuilder->setMimeTypeGroup('image');

        $this->assertEquals('mimetype_group=image', $queryBuilder->getQueryString());
    }

    /**
     * @covers Contentful\Query::__construct
     * @covers Contentful\Query::setContentType
     * @covers Contentful\Query::setLimit
     * @covers Contentful\Query::setSkip
     * @covers Contentful\Query::orderBy
     * @covers Contentful\Query::where
     * @covers Contentful\Query::getQueryData
     * @covers Contentful\Query::getQueryString
     */
    public function testFilterCombined()
    {
        $queryBuilder = new Query;
        $queryBuilder
            ->setContentType('cat')
            ->setLimit(150)
            ->setSkip(10)
            ->orderBy('sys.createdAt')
            ->where('sys.id', 'nyancat')
            ->where('sys.updatedAt', new \DateTimeImmutable('2013-01-01T00:00:00Z'), 'lte');

        $this->assertEquals('limit=150&skip=10&content_type=cat&order=sys.createdAt&sys.id=nyancat&sys.updatedAt%5Blte%5D=2013-01-01T00%3A00%3A00%2B00%3A00', (string) $queryBuilder->getQueryString());
    }
}

<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit;

use Contentful\Query;
use Contentful\Location;
use Contentful\Delivery\ContentType;

class FakeQuery extends Query
{
}

class QueryTest extends \PHPUnit_Framework_TestCase
{
    public function testFilterWithNoOptions()
    {
        $queryBuilder = new FakeQuery;

        $this->assertEquals('', $queryBuilder->getQueryString());
    }

    public function testFilterWithLimit()
    {
        $queryBuilder = new FakeQuery;
        $queryBuilder->setLimit(150);

        $this->assertEquals('limit=150', $queryBuilder->getQueryString());
    }

    /**
     * @expectedException \RangeException
     */
    public function testLimitThrowsOnValueTooLarge()
    {
        $queryBuilder = new FakeQuery;
        $queryBuilder->setLimit(1500);
    }

    /**
     * @expectedException \RangeException
     */
    public function testLimitThrowsOnValueZero()
    {
        $queryBuilder = new FakeQuery;
        $queryBuilder->setLimit(0);
    }

    /**
     * @expectedException \RangeException
     */
    public function testLimitThrowsOnValueNegative()
    {
        $queryBuilder = new FakeQuery;
        $queryBuilder->setLimit(-1);
    }

    public function testLimitSetNull()
    {
        $queryBuilder = new FakeQuery;
        $queryBuilder->setLimit(150);

        $queryBuilder->setLimit(null);

        $this->assertEquals('', $queryBuilder->getQueryString());
    }

    public function testFilterWithSkip()
    {
        $queryBuilder = new FakeQuery;
        $queryBuilder->setSkip(10);

        $this->assertEquals('skip=10', $queryBuilder->getQueryString());
    }

    /**
     * @expectedException \RangeException
     */
    public function testSkipThrowsOnValueNegative()
    {
        $queryBuilder = new FakeQuery;
        $queryBuilder->setSkip(-1);
    }

    public function testFilterOrderBy()
    {
        $queryBuilder = new FakeQuery;
        $queryBuilder->orderBy('sys.createdAt');

        $this->assertEquals('order=sys.createdAt', $queryBuilder->getQueryString());
    }

    public function testFilterOrderByReversed()
    {
        $queryBuilder = new FakeQuery;
        $queryBuilder->orderBy('sys.createdAt', true);

        $this->assertEquals('order=-sys.createdAt', $queryBuilder->getQueryString());
    }

    public function testFilterOrderByMultiple()
    {
        $queryBuilder = (new FakeQuery)
            ->orderBy('sys.createdAt')
            ->orderBy('sys.updatedAt', true);

        $this->assertEquals('order=sys.createdAt%2C-sys.updatedAt', $queryBuilder->getQueryString());
    }

    public function testGetSetContentTypeFromObject()
    {
        $queryBuilder = new FakeQuery;
        $contentType = $this->getMockBuilder(ContentType::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contentType->method('getId')
            ->willReturn('cat');

        $queryBuilder->setContentType($contentType);

        $this->assertEquals('content_type=cat', $queryBuilder->getQueryString());
    }

    public function testFilterByContentType()
    {
        $queryBuilder = new FakeQuery;
        $queryBuilder->setContentType('cat');

        $this->assertEquals('content_type=cat', $queryBuilder->getQueryString());
    }

    public function testWhere()
    {
        $queryBuilder = new FakeQuery;
        $queryBuilder->where('sys.id', 'nyancat');

        $this->assertEquals('sys.id=nyancat', $queryBuilder->getQueryString());
    }

    public function testWhereOperator()
    {
        $queryBuilder = new FakeQuery;
        $queryBuilder->where('sys.id', 'nyancat', 'ne');

        $this->assertEquals('sys.id%5Bne%5D=nyancat', $queryBuilder->getQueryString());
    }

    public function testWhereDateTime()
    {
        $queryBuilder = new FakeQuery;
        $queryBuilder->where('sys.updatedAt', new \DateTimeImmutable('2013-01-01T00:00:00Z'), 'lte');

        $this->assertEquals('sys.updatedAt%5Blte%5D=2013-01-01T00%3A00%3A00%2B00%3A00', $queryBuilder->getQueryString());
    }

    public function testWhereDateTimeResetsSeconds()
    {
        $queryBuilder = new FakeQuery;
        $queryBuilder->where('sys.updatedAt', new \DateTimeImmutable('2013-01-01T12:30:35Z'), 'lte');

        $this->assertEquals('sys.updatedAt%5Blte%5D=2013-01-01T12%3A30%3A00%2B00%3A00', $queryBuilder->getQueryString());
    }

    public function testWhereLocation()
    {
        $queryBuilder = new FakeQuery;
        $queryBuilder->where('fields.center', new Location(15.0, 17.8), 'near');

        $this->assertEquals('fields.center%5Bnear%5D=15%2C17.8', $queryBuilder->getQueryString());
    }

    public function testWhereArray()
    {
        $queryBuilder = new FakeQuery;
        $queryBuilder->where('fields.favoriteColor', ['blue', 'red'], 'all');

        $this->assertEquals('fields.favoriteColor%5Ball%5D=blue%2Cred', $queryBuilder->getQueryString());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWhereUnknownOperator()
    {
        $queryBuilder = new FakeQuery;
        $queryBuilder->where('sys.id', 'nyancat', 'wrong');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetMimeTypeGroupInvalid()
    {
        $queryBuilder = new FakeQuery;
        $queryBuilder->setMimeTypeGroup('invalid');
    }

    public function testFilterByMimeTypeGroup()
    {
        $queryBuilder = new FakeQuery;
        $queryBuilder->setMimeTypeGroup('image');

        $this->assertEquals('mimetype_group=image', $queryBuilder->getQueryString());
    }

    public function testFilterCombined()
    {
        $queryBuilder = new FakeQuery;
        $queryBuilder
            ->setContentType('cat')
            ->setLimit(150)
            ->setSkip(10)
            ->orderBy('sys.createdAt')
            ->where('sys.id', 'nyancat')
            ->where('sys.updatedAt', new \DateTimeImmutable('2013-01-01T00:00:00Z'), 'lte');

        $this->assertEquals('limit=150&skip=10&content_type=cat&order=sys.createdAt&sys.id=nyancat&sys.updatedAt%5Blte%5D=2013-01-01T00%3A00%3A00%2B00%3A00', (string) $queryBuilder->getQueryString());
    }

    public function testQueryWithSelect()
    {
        $queryBuilder = (new FakeQuery)
            ->select(['foobar1'])
            ->setContentType('cat');

        $this->assertEquals('content_type=cat&select=sys%2Cfoobar1', $queryBuilder->getQueryString());

        $queryBuilder = (new FakeQuery)
            ->select(['foobar2'])
            ->setContentType('cat');

        $this->assertEquals('content_type=cat&select=sys%2Cfoobar2', $queryBuilder->getQueryString());

        $queryBuilder = (new FakeQuery)
            ->select(['sys'])
            ->setContentType('cat');

        $this->assertEquals('content_type=cat&select=sys', $queryBuilder->getQueryString());
    }
}

<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\Delivery;

use Contentful\Delivery\Query;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Contentful\Delivery\Query::__construct
     * @covers Contentful\Delivery\Query::setInclude
     * @covers Contentful\Delivery\Query::getQueryData
     * @covers Contentful\Query::getQueryData
     * @covers Contentful\Query::getQueryString
     */
    public function testQueryStringInclude()
    {
        $query = new Query();
        $query->setInclude(50);

        $this->assertEquals('include=50', $query->getQueryString());
    }

    /**
     * @covers Contentful\Delivery\Query::__construct
     * @covers Contentful\Delivery\Query::setLocale
     * @covers Contentful\Delivery\Query::getQueryData
     * @covers Contentful\Query::getQueryData
     * @covers Contentful\Query::getQueryString
     */
    public function testQueryStringLocale()
    {
        $query = new Query();
        $query->setLocale('de-DE');

        $this->assertEquals('locale=de-DE', $query->getQueryString());
    }
}

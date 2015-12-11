<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\Delivery\Synchronization;

use Contentful\Delivery\Synchronization\Result;

class ResultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Contentful\Delivery\Synchronization\Result::__construct
     * @covers Contentful\Delivery\Synchronization\Result::getItems
     * @covers Contentful\Delivery\Synchronization\Result::getToken
     * @covers Contentful\Delivery\Synchronization\Result::isDone
     */
    public function testGetter()
    {
        $arr = [];
        $result = new Result($arr, 'token', false);

        $this->assertSame($arr, $result->getItems());
        $this->assertEquals('token', $result->getToken());
        $this->assertFalse($result->isDone());
    }
}

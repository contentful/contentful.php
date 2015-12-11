<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\Delivery;

use Contentful\Delivery\Link;

class LinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Contentful\Delivery\Link::__construct
     * @covers Contentful\Delivery\Link::getId
     * @covers Contentful\Delivery\Link::getLinkType
     */
    public function testGetter()
    {
        $link = new Link('123', 'Entry');

        $this->assertEquals('123', $link->getId());
        $this->assertEquals('Entry', $link->getLinkType());
    }
}

<?php

namespace Contentful\Tests\Unit\Delivery;

use Contentful\Delivery\DynamicEntry;
use Contentful\Delivery\InstanceCache;
use Contentful\Exception\InvalidArgumentException;

class InstanceCacheTest extends \PHPUnit_Framework_TestCase
{
    protected $instanceCache = null;
    protected $dynamicEntryMock = null;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->instanceCache = new InstanceCache();
        $this->dynamicEntryMock = $this->getMockBuilder(DynamicEntry::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @test
     */
    public function testSetWithoutTTL()
    {
        try {
            $result = $this->instanceCache->set('key', $this->dynamicEntryMock);
        } catch (InvalidArgumentException $e) {
            $this->fail($e->getMessage());
        }

        $this->assertTrue($result);
    }

    /**
     * @test
     * @depends testSetWithoutTTL
     */
    public function testGetWithExistingKey()
    {
        $this->instanceCache->set('key', $this->dynamicEntryMock);
        $result = $this->instanceCache->get('key', null);
        $this->assertEquals($result, $this->dynamicEntryMock);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function testSetWithInvalidTTL()
    {
        $result = $this->instanceCache->set('key', $this->dynamicEntryMock, 'blubb');
    }

    /**
     * @test
     * @depends testSetWithoutTTL
     */
    public function testHas()
    {
        $this->instanceCache->set('key', $this->dynamicEntryMock);
        $result = $this->instanceCache->has('key');
        $this->assertTrue($result);
    }

    /**
     * @test
     * @depends testSetWithoutTTL
     */
    public function testHasWithNotExistingKey()
    {
        $result = $this->instanceCache->has('key');
        $this->assertFalse($result);
    }
}

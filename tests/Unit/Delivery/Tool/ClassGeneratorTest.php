<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\Delivery\Tool;

use Contentful\Delivery\Tool\ClassGenerator;
use Contentful\Delivery\ContentType;

class ClassGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function getClassProvider()
    {
        return [
            ['human', 'EntryHuman']
        ];
    }

    /**
     * @dataProvider getClassProvider
     */
    public function testGetClass($id, $className)
    {
       $generator = new ClassGenerator;
       $contentType = $this->getMockBuilder(ContentType::class)
           ->disableOriginalConstructor()
           ->getMock();

       $contentType->method('getId')
           ->willReturn($id);

        $this->assertEquals($className, $generator->getClassName($contentType));
    }
}

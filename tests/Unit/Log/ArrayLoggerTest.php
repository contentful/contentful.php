<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Log;

use Contentful\Log\ArrayLogger;
use Contentful\Log\StandardTimer;
use Contentful\Log\TimerInterface;

class ArrayLoggerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetTimerSatisfiesInterface()
    {
        $logger = new ArrayLogger;

        $this->assertInstanceOf(TimerInterface::class, $logger->getTimer());
    }

    public function testGetTimerIsAlwaysNullTimer()
    {
        $logger = new ArrayLogger;

        $this->assertInstanceOf(StandardTimer::class, $logger->getTimer());
    }
}

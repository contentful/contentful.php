<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Log;

use Contentful\Log\NullLogger;
use Contentful\Log\NullTimer;
use Contentful\Log\TimerInterface;

class NullLoggerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetTimerSatisfiesInterface()
    {
        $logger = new NullLogger;

        $this->assertInstanceOf(TimerInterface::class, $logger->getTimer());
    }

    public function testGetTimerIsAlwaysNullTimer()
    {
        $logger = new NullLogger;

        $this->assertInstanceOf(NullTimer::class, $logger->getTimer());
    }
}

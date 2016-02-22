<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Log;

use Contentful\Log\StandardTimer;

class StandardTimerTest extends \PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $timer = new StandardTimer;

        $this->assertFalse($timer->isRunning());
        $this->assertNull($timer->getDuration());
    }

    public function testTimerOperation()
    {
        $timer = new StandardTimer;

        $timer->start();
        $this->assertTrue($timer->isRunning());
        $this->assertNull($timer->getDuration());

        $timer->stop();
        $this->assertFalse($timer->isRunning());
        $this->assertInternalType('float', $timer->getDuration());
        $this->assertGreaterThan(0.0, $timer->getDuration());
    }

    public function testTimerCanNotBeRestarted()
    {
        $timer = new StandardTimer;

        $timer->start();
        $timer->stop();
        $timer->start();
        $this->assertFalse($timer->isRunning());
    }

    public function testStoppingBeforeStartingDoesNothing()
    {
        $timer = new StandardTimer;

        $timer->stop();
        $timer->start();
        $timer->stop();
        $this->assertFalse($timer->isRunning());
        $this->assertInternalType('float', $timer->getDuration());
        $this->assertGreaterThan(0.0, $timer->getDuration());
    }
}

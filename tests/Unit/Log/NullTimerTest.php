<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Log;

use Contentful\Log\NullTimer;

class NullTimerTest extends \PHPUnit_Framework_TestCase
{
    public function testTimerNeverRuns()
    {
        $timer = new NullTimer;

        $this->assertFalse($timer->isRunning());
        $timer->start();
        $this->assertFalse($timer->isRunning());
    }

    public function testTimerDurationAlwaysNull()
    {
        $timer = new NullTimer;

        $this->assertNull($timer->getDuration());
        $timer->start();
        $this->assertNull($timer->getDuration());
        $timer->stop();
        $this->assertNull($timer->getDuration());
    }
}

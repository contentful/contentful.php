<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Log;

class StandardTimer implements TimerInterface
{
    /**
     * @var float
     */
    private $startTime = 0.0;

    /**
     * @var float
     */
    private $endTime = 0.0;

    /**
     * @var bool
     */
    private $isStarted = false;

    /**
     * @var bool
     */
    private $isStopped = false;

    /**
     * Empty constructor for forward compatibility.
     */
    public function __construct()
    {
    }

    /**
     * Starts the timer.
     *
     * @return void
     */
    public function start()
    {
        if ($this->isStarted) {
            return;
        }
        $this->startTime = microtime(true);
        $this->isStarted = true;
    }

    /**
     * Stops the timer.
     *
     * @return void
     */
    public function stop()
    {
        if ($this->isStopped || !$this->isStarted) {
            return;
        }
        $this->endTime = microtime(true);
        $this->isStopped = true;
    }

    /**
     * Returns true if the timer is currently running.
     *
     * @return bool
     */
    public function isRunning()
    {
        return $this->isStarted && !$this->isStopped;
    }

    /**
     * Returns the time (in seconds) the timer ran.
     *
     * Returns null if no time has been recorded.
     *
     * @return float|null
     */
    public function getDuration()
    {
        if (!$this->isStarted || !$this->isStopped) {
            return null;
        }

        return $this->endTime - $this->startTime;
    }
}

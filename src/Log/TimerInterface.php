<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Log;

/**
 * The TimerInterface is used to record the duration of requests against Contentful's API.
 */
interface TimerInterface
{
    /**
     * Starts the timer.
     *
     * @return void
     */
    public function start();

    /**
     * Stops the timer.
     *
     * @return void
     */
    public function stop();

    /**
     * Returns true if the timer is currently running.
     *
     * @return bool
     */
    public function isRunning();

    /**
     * Returns the time (in seconds) the timer ran.
     *
     * Returns null if no time has been recorded.
     *
     * @return float|null
     */
    public function getDuration();
}

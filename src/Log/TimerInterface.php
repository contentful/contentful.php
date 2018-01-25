<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
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
     */
    public function start();

    /**
     * Stops the timer.
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

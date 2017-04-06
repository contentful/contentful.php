<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Log;

/**
 * Implementation of TimerInterface that does not actually record any time.
 *
 * Used in production to reduce overhead.
 */
class NullTimer implements TimerInterface
{
    /**
     * Empty constructor for forward compatibility.
     */
    public function __construct()
    {
    }

    public function start()
    {
    }

    public function stop()
    {
    }

    /**
     * @return bool
     */
    public function isRunning()
    {
        return false;
    }

    /**
     * @return null
     */
    public function getDuration()
    {
        return null;
    }
}

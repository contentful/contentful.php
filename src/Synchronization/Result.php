<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Synchronization;

/**
 * The Result of synchronization.
 */
class Result
{
    /**
     * @var array
     */
    private $items;

    /**
     * @var string
     */
    private $token;

    /**
     * @var bool
     */
    private $done;

    /**
     * Result constructor.
     *
     * @param array  $items
     * @param string $token
     * @param bool   $done
     */
    public function __construct(array $items, $token, $done)
    {
        $this->items = $items;
        $this->token = $token;
        $this->done = $done;
    }

    /**
     * Returns the items retrieved by this synchronization operation.
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Returns the token needed to continue the synchronization.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Returns true if there are currently no more results in the synchronization.
     *
     * @return bool
     */
    public function isDone()
    {
        return $this->done;
    }
}

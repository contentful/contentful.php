<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Synchronization;

/**
 * The Result of synchronization.
 *
 * @api
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
     *
     * @api
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Returns the token needed to continue the synchronization.
     *
     * @return string
     *
     * @api
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Returns true if there are currently no more results in the synchronization.
     *
     * @return bool
     *
     * @api
     */
    public function isDone()
    {
        return $this->done;
    }
}

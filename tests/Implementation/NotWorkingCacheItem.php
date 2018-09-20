<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Implementation;

use Psr\Cache\CacheItemInterface;

class NotWorkingCacheItem implements CacheItemInterface
{
    private $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function get()
    {
        return \null;
    }

    public function isHit()
    {
        return \false;
    }

    public function set($value)
    {
        return $this;
    }

    public function expiresAt($expiration)
    {
        return $this;
    }

    public function expiresAfter($time)
    {
        return $this;
    }
}

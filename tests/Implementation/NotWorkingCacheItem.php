<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2022 Contentful GmbH
 * @license   MIT
 */


namespace Contentful\Tests\Delivery\Implementation;

use Psr\Cache\CacheItemInterface;

class NotWorkingCacheItem implements CacheItemInterface
{
    /**
     * @var string
     */
    private $key;

    /**
     * NotWorkingCacheItem constructor.
     */
    public function __construct(string $key)
    {
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function get() : ?self
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isHit(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function set($value) : self
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAt($expiration) : self
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAfter($time) : self
    {
        return $this;
    }
}

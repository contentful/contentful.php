<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class ProxyCacheItemPool implements CacheItemPoolInterface
{
    /**
     * @var CacheItemPoolInterface
     */
    private $wrapped;

    /**
     * @var bool
     */
    private $readOnly;

    /**
     * ProxyCacheItemPool constructor.
     *
     * @param CacheItemPoolInterface $wrapped
     * @param bool                   $readOnly
     */
    public function __construct(CacheItemPoolInterface $wrapped, bool $readOnly)
    {
        $this->wrapped = $wrapped;
        $this->readOnly = $readOnly;
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key): CacheItemInterface
    {
        return $this->wrapped->getItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = [])
    {
        return $this->wrapped->getItems($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key): bool
    {
        return $this->wrapped->hasItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->readOnly
            ? \true
            : $this->wrapped->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key)
    {
        return $this->readOnly
            ? \true
            : $this->wrapped->deleteItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        return $this->readOnly
            ? \true
            : $this->wrapped->deleteItems($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item): bool
    {
        return $this->readOnly
            ? \true
            : $this->wrapped->save($item);
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        return $this->readOnly
            ? \true
            : $this->wrapped->saveDeferred($item);
    }

    /**
     * {@inheritdoc}
     */
    public function commit(): bool
    {
        return $this->readOnly
            ? \true
            : $this->wrapped->commit();
    }
}

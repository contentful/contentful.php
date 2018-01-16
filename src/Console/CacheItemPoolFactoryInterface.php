<?php

namespace Contentful\Console;

use Psr\Cache\CacheItemPoolInterface;

interface CacheItemPoolFactoryInterface
{
    public function __construct();

    /**
     * @return CacheItemPoolInterface
     */
    public function getCacheItemPool();
}

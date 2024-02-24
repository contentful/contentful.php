<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Implementation;

use Contentful\Delivery\Cache\CacheItemPoolFactoryInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class CacheItemPoolFactory implements CacheItemPoolFactoryInterface
{
    /**
     * @var CacheItemPoolInterface[]
     */
    public static $pools = [];

    /**
     * CacheItemPoolFactory constructor.
     */
    public function __construct()
    {
        self::$pools = [];
    }

    public function getCacheItemPool(string $api, string $spaceId, string $environmentId): CacheItemPoolInterface
    {
        $key = $api.'.'.$spaceId.'.'.$environmentId;
        if (!isset(self::$pools[$key])) {
            self::$pools[$key] = new ArrayAdapter();
        }

        return self::$pools[$key];
    }
}

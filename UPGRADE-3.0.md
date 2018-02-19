# UPGRADE FROM 2.x to 3.0

## Change of namespace of resource classes

These resource classes have been moved:

| Before                                 | After                                            |
| -------------------------------------- | ------------------------------------------------ |
| `Contentful\Delivery\DynamicEntry`     | `Contentful\Delivery\Resource\Entry`             |
| `Contentful\Delivery\Asset`            | `Contentful\Delivery\Resource\Asset`             |
| `Contentful\Delivery\ContentType`      | `Contentful\Delivery\Resource\ContentType`       |
| `Contentful\Delivery\ContentTypeField` | `Contentful\Delivery\Resource\ContentType\Field` |
| `Contentful\Delivery\Space`            | `Contentful\Delivery\Resource\Space`             |
| `Contentful\Delivery\Locale`           | `Contentful\Delivery\Resource\Locale`            |

## Removal of EntryInterface

`Contentful\Delivery\EntryInterface` has been removed. If you need, use `Contentful\Delivery\Resource\Entry` for type hinting.

## Update cache mechanism

Up to `2.x`, the cache mechanism was simply storing the dumped JSON files in a local directory. In version 3.0, any [PSR-6 compatible cache pool](https://packagist.org/search/?tags=psr6) can be used. We suggest using `symfony/cache` for a comprehensive solution (which also supports namespacing of cache items, ideal for storing data of multiple Contentful spaces), or a specific [cache adapter](https://packagist.org/packages/cache/). Bear in mind that the Symfony package provides [adapters](https://symfony.com/doc/current/components/cache/psr6_psr16_adapters.html) for interoperability between PSR-6 and PSR-16 cache implementations, so it might be useful if in your projects you're already implementing PSR-16.

To warm up the cache using the new system, you have 2 options: CLI and code.

### CLI

To implement the cache warming and clearing through CLI, you need to define a factory which implements `Contentful\Delivery\Cache\CacheItemPoolFactoryInterface`:

``` php
<?php

namespace App\Cache;

use Contentful\Delivery\Cache\CacheItemPoolFactoryInterface;
use Psr\Cache\CacheItemPoolInterface;

class AppCacheFactory extends CacheItemPoolFactoryInterface
{
    public function __construct()
    {
        // Optionally do some setup
    }

    /**
     * Returns a PSR-6 CacheItemPoolInterface object.
     * The method receives two parameters, which can be used
     * for things like using a different directory in the filesystem,
     * but the implementation can simply not use them if they're not necessary.
     *
     * @param string $api     A string representation of the API in use,
     *                        it's the result of calling $client->getApi()
     * @param string $spaceId The ID of the space
     *
     * @return CacheItemPoolInterface
     */
    public function getCacheItemPool($api, $spaceId)
    {
        // ...

        return $cacheItemPool;
    }
}
```

And then, call it like this:

``` bash
# Warm up the cache for the Delivery API
php vendor/bin/contentful delivery:cache:warmup "<spaceId>" "<accessToken>" "\\App\\Cache\\AppCacheFactory"

# Warm up the cache for the Preview API
php vendor/bin/contentful delivery:cache:warmup "<spaceId>" "<accessToken>" "\\App\\Cache\\AppCacheFactory" --use-preview

# Clear the cache for the Delivery API
php vendor/bin/contentful delivery:cache:clear "<spaceId>" "<accessToken>" "\\App\\Cache\\AppCacheFactory"

# Clear the cache for the Preview API
php vendor/bin/contentful delivery:cache:clear "<spaceId>" "<accessToken>" "\\App\\Cache\\AppCacheFactory" --use-preview
```

You should provide a fully-qualified class name of your factory, i.e. something which can be instantiated with a simple `$factory = new $factoryFqcn()`. These commands will create an instance of your class, and use the object returned from `getCacheItemPool` to store (or delete) data.

### Code

Behind the scenes, the CLI is actually using the class `Contentful\Delivery\Cache\CacheWarmer` to handle the process, so if you have special needs, you can use that yourself:

``` php
<?php

/** @var $client \Contentful\Delivery\Client */
/** @var $$cacheItemPool \Psr\Cache\CacheItemPoolInterface */

// To warm up the cache
$warmer = new \Contentful\Delivery\Cache\CacheWarmer($client, $cacheItemPool);
$warmer->warmUp();

// To clear the cache
$clearer = new \Contentful\Delivery\Cache\CacheClearer(client, $cacheItemPool);
$clearer->clear();
```

### Set up the client

Finally, you need to tell the client to use that cache. To do so, pass a `Psr\Cache\CacheItemPoolInterface` instance in the options array:

``` php
<?php

$client = new \Contentful\Delivery\Client(
    $token,
    $spaceId,
    $preview = false,
    $defaultLocale = null,
    ['cache' => $cacheItemPool, 'autoWarmup' => true]
);
```

The default value of the `autoWarmup` option is `false`. When you set it to `true`, the SDK will add items to your cache as it encounters them through natural use. This enables you to have more dynamic setups, for instance if the credentials (space ID and access tokens) are not available at build time, you can have the cache be populated on a per-need basis. This also means that you can clear the cache without worrying for warming it up: in a situation where your content types may change often, you'd only need to set up a webhook to clear the cache, and then leave the warming-up part to natural use.

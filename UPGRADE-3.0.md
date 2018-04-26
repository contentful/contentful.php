# UPGRADE FROM 2.x to 3.0

## Change of signature of client constructor

The `Contentful\Delivery\Client` constructor now takes a new third argument, called `$environmentId`, which defaults to `master`. All remaining parameters have shifted to the right, so for instance the flag for using the Preview API will now be parameter 4 instead of 3.

The `uriOverride` key in the options array has been renamed to `baseUri`, to better reflect its meaning and conform to the terminology actually used by the underlying Guzzle client.

``` php
<?php

use Contentful\Delivery\Client;

// Previously
$client = new Client($accessToken, $spaceId, $usePreview = false, $defaultLocale = null, $options = ['uriOverride' => '...']);

// Now
$client = new Client($accessToken, $spaceId, $environmentId = 'master', $usePreview = false, $defaultLocale = null, $options = ['baseUri' => '...']);
```

## Change of namespace of some classes

While in a process of restructuring the SDK, a number of classes were moved to a different location. However, the contents of the classes have not substantially changed, which means that a global search/replace in your projects will allow you to upgrade smoothly. If, for whatever reason you don't want to do that, the SDK also provides a drop-in compat layer which allows you to just include a file which will define aliases from new classes to the old ones. To include this compat layer, just add this to the `"autoload"` section in your `composer.json` file, and then run `composer dump-autoload`:

``` json
{
    "autoload": {
         "files": ["vendor/contentful/contentful/extra/class_aliases.php"],
    }
}
```

These resource classes have been moved to the `Contentful\Delivery\Resource` namespace:

| Before                                                   | After                                             |
| -------------------------------------------------------- | ------------------------------------------------- |
| `Contentful\Delivery\DynamicEntry`                       | `Contentful\Delivery\Resource\Entry`              |
| `Contentful\Delivery\Asset`                              | `Contentful\Delivery\Resource\Asset`              |
| `Contentful\Delivery\ContentType`                        | `Contentful\Delivery\Resource\ContentType`        |
| `Contentful\Delivery\ContentTypeField`                   | `Contentful\Delivery\Resource\ContentType\Field`  |
| `Contentful\Delivery\Space`                              | `Contentful\Delivery\Resource\Space`              |
| `Contentful\Delivery\Locale`                             | `Contentful\Delivery\Resource\Locale`             |
| `Contentful\Delivery\Synchronization\DeletedResource`    | `Contentful\Delivery\Resource\DeletedResource`    |
| `Contentful\Delivery\Synchronization\DeletedAsset`       | `Contentful\Delivery\Resource\DeletedAsset`       |
| `Contentful\Delivery\Synchronization\DeletedContentType` | `Contentful\Delivery\Resource\DeletedContentType` |
| `Contentful\Delivery\Synchronization\DeletedEntry`       | `Contentful\Delivery\Resource\DeletedEntry`       |

These classes have been moved to a separate package, which is required from the SDK, but places classes in a different namespace:

| Before                                             | After                                                   |
| -------------------------------------------------- | ------------------------------------------------------- |
| `Contentful\Exception\AccessTokenInvalidException` | `Contentful\Core\Exception\AccessTokenInvalidException` |
| `Contentful\Exception\ApiException`                | `Contentful\Core\Api\Exception`                         |
| `Contentful\Exception\BadRequestException`         | `Contentful\Core\Exception\BadRequestException`         |
| `Contentful\Exception\InvalidQueryException`       | `Contentful\Core\Exception\InvalidQueryException`       |
| `Contentful\Exception\NotFoundException`           | `Contentful\Core\Exception\NotFoundException`           |
| `Contentful\Exception\RateLimitExceededException`  | `Contentful\Core\Exception\RateLimitExceededException`  |
| `Contentful\File\File`                             | `Contentful\Core\File\File`                             |
| `Contentful\File\FileInterface`                    | `Contentful\Core\File\FileInterface`                    |
| `Contentful\File\ImageFile`                        | `Contentful\Core\File\ImageFile`                        |
| `Contentful\File\ImageOptions`                     | `Contentful\Core\File\ImageOptions`                     |
| `Contentful\File\LocalUploadFile`                  | `Contentful\Core\File\LocalUploadFile`                  |
| `Contentful\File\RemoteUploadFile`                 | `Contentful\Core\File\RemoteUploadFile`                 |
| `Contentful\File\UnprocessedFileInterface`         | `Contentful\Core\File\UnprocessedFileInterface`         |
| `Contentful\Link`                                  | `Contentful\Core\Api\Link`                              |
| `Contentful\Location`                              | `Contentful\Core\Api\Location`                          |
| `Contentful\ResourceArray`                         | `Contentful\Core\Resource\ResourceArray`                |

## Changes in `Client::reviveJson()`

`Client::reviveJson()` was a method that allowed you to parse a JSON string that is in a format recognized from the SDK, and turn it into a resource object. In order to better reflect its meaning, it was renamed to `Client::parseJson()`. Previously it would throw a custom exception of type `Contentful\Exception\SpaceMismatchException`, which has since been removed; instead, if trying to parse a JSON string with unrecognized space or environment ID, an exception of type `\InvalidArgumentException` will now be thrown.

## Removal of EntryInterface and other deprecated classes

* `Contentful\Delivery\EntryInterface` has been removed. If you need, use directly `Contentful\Delivery\Resource\Entry` for type hinting.
* `Contentful\JsonHelper` (deprecated since 2.2): it was a class used internally and this should not have effects on users' codebases, however if for whatever reason you were using it, you should replace `JsonHelper::decode()` with `GuzzleHttp\json_decode()` and `JsonHelper::encode()` with `GuzzleHttp\json_decode()`, which wrap PHP standard functions in order to provide better error handling.
* `Contentful\DateHelper` (deprecated since 2.2): if you were using `DateHelper::formatForJson()` directly, replace those uses with `Contentful\format_date_for_json()`, which provides the same features.
* `Contentful\File\UploadFile` (deprecated since 2.1): if you were using this class for type hinting, you should replace it with `Contentful\Core\File\RemoteUploadFile`. This class provides the same function, and it's just a renamed version of the previous one, to better differentiate between that and `LocalUploadFile`. In fact, `UploadFile` was actually already an empty class extending `RemoteUploadFile`.

## Updated cache mechanism

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
     * @param string $api           A string representation of the API in use,
     *                              it's the result of calling $client->getApi()
     * @param string $spaceId       The ID of the space
     * @param string $environmentId The ID of the environment
     *
     * @return CacheItemPoolInterface
     */
    public function getCacheItemPool($api, $spaceId, $environmentId)
    {
        // ...

        return $cacheItemPool;
    }
}
```

And then, call it like this:

``` bash
# Warm up the cache for the Delivery API
php vendor/bin/contentful delivery:cache:warmup --space-id=$SPACE_ID --access-token=$ACCESS_TOKEN --environment-id=$ENVIRONMENT_ID --factory-class="App\\Cache\\AppCacheFactory"

# Warm up the cache for the Preview API
php vendor/bin/contentful delivery:cache:warmup --space-id=$SPACE_ID --access-token=$ACCESS_TOKEN --environment-id=$ENVIRONMENT_ID --factory-class="App\\Cache\\AppCacheFactory" --use-preview

# Clear the cache for the Delivery API
php vendor/bin/contentful delivery:cache:clear --space-id=$SPACE_ID --access-token=$ACCESS_TOKEN --environment-id=$ENVIRONMENT_ID --factory-class="App\\Cache\\AppCacheFactory"

# Clear the cache for the Preview API
php vendor/bin/contentful delivery:cache:clear --space-id=$SPACE_ID --access-token=$ACCESS_TOKEN --environment-id=$ENVIRONMENT_ID --factory-class="App\\Cache\\AppCacheFactory" --use-preview
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
$clearer = new \Contentful\Delivery\Cache\CacheClearer($client, $cacheItemPool);
$clearer->clear();
```

### Set up the client

Finally, you need to tell the client to use that cache. To do so, pass a `Psr\Cache\CacheItemPoolInterface` instance in the options array:

``` php
<?php

$client = new \Contentful\Delivery\Client(
    $token,
    $spaceId,
    $environmentId = 'master',
    $preview = false,
    $defaultLocale = null,
    ['cache' => $cacheItemPool, 'autoWarmup' => true]
);
```

The default value of the `autoWarmup` option is `false`. When you set it to `true`, the SDK will add items to your cache as it encounters them through natural use. This enables you to have more dynamic setups, for instance if the credentials (space ID and access tokens) are not available at build time, you can have the cache be populated on a per-need basis. This also means that you can clear the cache without worrying for warming it up: in a situation where your content types may change often, you'd only need to set up a webhook to clear the cache, and then leave the warming-up part to natural use.


### Updated logging mechanism

The SDK no longer uses a custom logging system, and is instead PSR-3 compatible. You can now pass any logger implementing `Psr\Log\LoggerInterface` to the client constructor:

``` php
<?php

use Contentful\Delivery\Client;

$client = new Client(
    $accessToken,
    $spaceId,
    $environmentId = 'master',
    $usePreview = false,
    $defaultLocale = null,
    $options = [
        'logger' => $myLogger,
    ]
);
```

The SDK currently only uses two levels of logging: `INFO` for regular requests, and `ERROR` for requests in the `400` and `500` range.

The previous mechanism allowed you to optionally pass an instance of `Contentful\Log\ArrayLogger` in order to store information about the requests you made. This behavior is now built-in and you don't have to do anything manually. You can simply use `Client::getMessages()` to access an array of `Contentful\Core\Api\Message` objects, which contain useful information for debugging, such as the raw PSR-7 requests and response objects, the duration of the API call, and the exception object generated from the response (if any).

Because of how PSR-3 loggers work, message objects are serialized as JSON strings before being sent to the logger. You can use `Message::createFromString($json)` to reconstruct the original message object, but bear in mind that some bits of information get lost in the serialization process, such as the difference between `http` and `https` in the request host, and the trace data of the exception.

### Disallowed use of fallback chain except when using the `*` locale

The SDK internally reproduces the locale fallback chain mechanism that is built into the Delivery and Preview API. This is useful in situations where you request the full resource with all its locales, but you still want to be able to use locale fallbacks as if you were requesting them from the API itself. Unfortunately, the SDK would use the chain in situations where this could possibly lead to field values inconsistent with those stored in Contentful.

Let's look at an example to illustrate the possibly problematic behavior. Let's say you have an environment whose locales look like this:

* `en-US` (default)
* `it-IT` (falls back to `en-US`)

Now, imagine having an entry with a field called `name`, with the following values:

* `en-US`: "_House_"
* `it-IT`: "_Casa_"

This is what would happen previously (in the SDK v2):

``` php
// No locale is provided, so Contentful will use the default one, en-US
$client->getEntry($entryId);

$entry->getName(); // House
$entry->getName('en-US'); // House
$entry->getName('it-IT'); // House


// Now, all locales are requested
$client->getEntry($entryId, '*');

$entry->getName(); // House
$entry->getName('en-US'); // House
$entry->getName('it-IT'); // Casa
```

As you can see, `$entry->getName('it-IT')` yields two different results, because the fallback chain is being used with an incomplete dataset.

In order to avoid this situation, the SDK now forbids access to a locale which is different from the one used to fetch the entry. If you want to access multiple locales on a single entry, you need to use the `locale=*` value:

``` php
// No locale is provided, so Contentful will use the default one, en-US
$client->getEntry($entryId);

$entry->getName(); // House
$entry->getName('en-US'); // House
$entry->getName('it-IT'); // InvalidArgumentException: Entry with ID "<entryId>" was built using locale "en-US", but now access using locale "it-IT" is being attempted.


// Now, all locales are requested
$client->getEntry($entryId, '*');

$entry->getName(); // House
$entry->getName('en-US'); // House
$entry->getName('it-IT'); // Casa
```

### Removed shortcuts from resource classes

`Asset`, `ContentType`, `Entry`, and `DeletedResource` classes previously provided shortcut methods for accessing system properties such as revision, createdAt, etc. These shortcut methods have been removed from the main resource, but they're still accessible through a `Contentful\Delivery\SystemProperties` object.

``` php
// Before
$entry->getRevision();
// After
$entry->getSystemProperties()->getRevision();

// Before
$asset->getUpdatedAt();
// After
$asset->getSystemProperties()->getUpdatedAt();

// Before
$contentType->getCreatedAt();
// After
$contentType->getSystemProperties()->getCreatedAt();
```

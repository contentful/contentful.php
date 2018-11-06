# UPGRADE FROM 3.x to 4.0

## Usage of PHP 7.0 and strict types

The SDK now requires PHP 7.0, and it employs the `declare(strict_types=1)` directive everywhere. All methods use type declarations whenever possible, so in case you are extending some SDK classes, you will need to update your code accordingly.

## Change of signature of client constructor

The client now accepts 3 string parameters, and a `Contentful\Delivery\ClientOptions` object. These are the before and after:

### Using the Preview API

``` php
// Before
new Client($token, $spaceId, $environmentId, true);

// After
$options = ClientOptions::create()
    ->usingPreviewApi();
new Client($token, $spaceId, $environmentId, $options);
```

### Using a default locale

``` php
$defaultLocale = 'en-US';

// Before
new Client($token, $spaceId, $environmentId, false, $defaultLocale);

// After
$options = ClientOptions::create()
    ->withDefaultLocale($defaultLocale);
new Client($token, $spaceId, $environmentId, $options);
```
### Using a PSR-6 cache

``` php
$psr6CacheItemPool = ...;
$autoCacheWarmup = true;
$cacheContent = true;

// Before
new Client($token, $spaceId, $environmentId, false, null, [
    'cache' => $psr6CacheItemPool,
    'autoWarmup' => $autoCacheWarmup,
    'cacheContent' => $cacheContent,
]);

// After
$options = ClientOptions::create()
    ->withCache($psr6CacheItemPool, $autoCacheWarmup, $cacheContent);
new Client($token, $spaceId, $environmentId, $options);
```

---

These are the most common options, but it's also allowed to use a custom PSR-3 logger using `ClientOptions::withLogger(Psr\Log\LoggerInterface $logger)`, configuring a custom API host (useful when using a proxy) with `ClientOptions::withHost(string $host)`, and setting a custom Guzzle client with `ClientOptions::withHttpClient(GuzzleHttp\Client $client)`.

All methods of `ClientOptions` are chainable and can be combined in any way. For instance, using a custom locale with caching and the Preview API would look like this:

``` php
$options = ClientOptions::create()
    ->usePreviewApi()
    ->withDefaultLocale($defaultLocale)
    ->withCache($psr6CacheItemPool, $autoCacheWarmup, $cacheContent);
new Client($token, $spaceId, $environmentId, $options);
```

## Introduction of ClientInterface

Interface `Contentful\Delivery\Client\ClientInterface` was created, and it is implemented by the standard client. It is recommended to use this interface when passing the client instance around, as it decouples your code from the actual implementation, helping with testing scenarios.

## Removal of third parameter in Query::where()

The `Contentful\Delivery\Query::where()` method used to accept a third parameter, which was used to specify the type of search (for instance with operators `near` or `lte`). As many users did not know this and appended the operator to the first parameter of `where()`, now the third parameter was removed, and the correct way of using the operator is to add it to the first parameter:

``` php
// Before
$query->where('fields.age', 18, 'gte');

// After
$query->where('fields.age[gte]', 18);
```

## Change of name for instance repository

The `Contentful\Delivery\InstanceRepository` class was renamed `Contentful\Delivery\ResourcePool`, and therefore the `Client::getInstanceRepository()` method was renamed `Client::getResourcePool()`.

## Strict types in CacheItemPoolFactoryInterface

Interface `Contentful\Delivery\Cache\CacheItemPoolFactoryInterface` no longer declares a constructor, however remember that the class is still supposed to be instantiated from the CLI commands without arguments. Furthermore, its method `getCacheItemPool` now uses explicit type declaration for parameters and return value, so your classes implementing this interface will have to be updated accordingly. 

## Change of handling for SystemProperties

Previously, all resources shared a common implementation of the system properties object, located in `Contentful\Delivery\SystemProperties`. The issue with implementation was that in order to accommodate for all possible resources, the class was unnecessarily big and contained many nullable properties.

To fix this and make the handling of system properties more robust, now every resource declares a specific system properties class, which is enforced through strict typing in their corresponding `->getSystemProperties()` methods:

```php
/** @var \Contentful\Delivery\SystemProperties\Entry $sys */
$sys = $entry->getSystemProperties();

/** @var \Contentful\Delivery\SystemProperties\Asset $sys */
$sys = $asset->getSystemProperties();

/** @var \Contentful\Delivery\SystemProperties\ContentType $sys */
$sys = $contentType->getSystemProperties();
```

The system properties objects will work just like before, but pay attention to two things:

* If you were type hinting against general `Contentful\Delivery\SystemProperties` class, you need to change that to something more specific. If you still need something generic, you can use the base `Contentful\Core\Resource\SystemPropertiesInterface`, but be careful as it only defines the `getId()` and `getType()` methods.
* Methods that conceptually did not belong to a resource's system properties will no longer exist. For instance, you will not find the `getDeletedAt()` method in the `Contentful\Delivery\SystemProperties\Space` class.

# Changelog

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased](https://github.com/contentful/contentful.php/compare/7.1.0...HEAD)

<!-- PENDING-CHANGES -->
> No meaningful changes since last release.
<!-- /PENDING-CHANGES -->

## [7.1.0](https://github.com/contentful/contentful.php/tree/7.1.0) (2024-07-25)

### Added

* Support for recursive resolution of Entry links

## [7.0.2](https://github.com/contentful/contentful.php/tree/7.0.2) (2024-02-24)

### Changed

* Added support for PHP8.3
* Added support for Symfony 7

### Internal

* Updated header comments
* Re-enabled unit tests for PHP8.2


## [7.0.1](https://github.com/contentful/contentful.php/tree/7.0.1) (2023-01-26)

### Changed

* Added support for PHP8.2
* Fixed cache handling for queries with default locale, see [#320](https://github.com/contentful/contentful.php/issues/320) - thanks @fabianaromagnoli !
* Re-added support for psr/log v1 - see [#322](https://github.com/contentful/contentful.php/issues/322)

### Internal

* Updated header comments
* Re-enabled unit tests for PHP8.2

## [7.0.0](https://github.com/contentful/contentful.php/tree/7.0.0) (2022-12-22)

### Changed

* **Breaking change**: Dropped support for PHP7
* **Breaking change**: Updated depdency for contentful/rich-text to 4.0, which has breaking changes - see [here](https://github.com/contentful/rich-text.php/releases/tag/4.0.0).
* Minor docs updates - thanks @mmenozzi !
* Fixed several deprecation warnings.

### Internal

* CI pipelines slightly reworked

### Notes

* The unit tests currently segfault on PHP8.2. This is a known PHP bug - see [here](https://github.com/php-vcr/php-vcr/issues/373).


## [6.4.0](https://github.com/contentful/contentful.php/tree/6.4.0) (2022-03-11)

### Added

* Support for caching of Client::getEntries() - thanks @mmenozzi !
* Support for psr/cache v2.0 - thanks @kwivix !

### Internal

* Small CI fixes
* Switched to new CircleCI PHP images

### Notes

* Due to some interface incompatibility, this version does currently not support psr/cache 3.0 - if you need 3.0, you can stay on 6.3.0 for now. We plan on supporting version 3 again in the future.

## [6.3.0](https://github.com/contentful/contentful.php/tree/6.3.0) (2022-01-19)

> Updated Symfony dependencies to allow for Symfony 6
> Added support for psr/cache version 3
> Added support for a backwards-compatibility breakage check

## [6.2.0](https://github.com/contentful/contentful.php/tree/6.2.0) (2021-10-17)

## Added

> Support for content tags

## Fixed

> Access to localized fields with a non-standard locale (see [#270](https://github.com/contentful/contentful.php/issues/270))

## Changed

> Name changed from "SDK" to "Library" to clarify distinction

## [6.1.0](https://github.com/contentful/contentful.php/tree/6.1.0) (2021-03-20)

## Added

> Support for PHP8

## Internal

> Small changes to the CI due to the migration

## [6.0.5](https://github.com/contentful/contentful.php/tree/6.0.5) (2020-10-19)

## Added

> withoutMessageLogging option

## [6.0.4](https://github.com/contentful/contentful.php/tree/6.0.4) (2020-09-15)

## Added

> Update packages
> Coding standards
> Fixed a few static code analysis issues

## [6.0.3](https://github.com/contentful/contentful.php/tree/6.0.3) (2020-04-03)

### Changed

> Minor non-breaking changes suggeste by phpstan

## [6.0.1](https://github.com/contentful/contentful.php/tree/6.0.1) (2020-03-13)

### Changed

> Upgraded symfony/console

## [6.0.0](https://github.com/contentful/contentful.php/tree/6.0.0) (2020-03-13)

### Changed

> Added support for PHP 7.4. Removed support for PHP 7.0 & 7.1. Updated dependencies.

## [5.0.2](https://github.com/contentful/contentful.php/tree/5.0.2) (2020-02-24)

### Fixed

* corrected composer dependency for richtext-php
* corrected failing e2e tests

## [5.0.0](https://github.com/contentful/contentful.php/tree/5.0.0) (2020-02-19)

### Changed

* Bumping major version due to breaking change in rich-text.php

## [4.1.4](https://github.com/contentful/contentful.php/tree/4.1.4) (2020-02-06)

### Fixed

* Cannot fetch large number of independent entries

## [4.1.3](https://github.com/contentful/contentful.php/tree/4.1.3) (2019-12-04)

* Fixed infinite loop when trying to lad a rich text entry that references itself

## [4.1.2](https://github.com/contentful/contentful.php/tree/4.1.2) (2019-11-21)

### Fixed
* Fixed issue with link resolution.
* Addressed cs-fixer syntax issues.

## [4.1.1](https://github.com/contentful/contentful.php/tree/4.1.1) (2019-01-18)

### Fixed
* Sync API now correctly works with deleted entries.
* The SDK now works correctly when using an access token which does not have access to the `master` environment.

## [4.1.0](https://github.com/contentful/contentful.php/tree/4.1.0) (2018-11-28)

### Added
* When working with huge datasets (tens of thousands of resources), for instance with custom CLI commands, the default resource pool may have caused the application to use too much memory and crash. Now there are two different resource pools: `Contentful\Delivery\ResourcePool\Standard`, which is a lightweight implemention and only caches space, environment and content types, and `Contentful\Delivery\ResourcePool\Extended`, which also keeps a reference to entries and assets. The latter class was known as `Contentful\Delivery\ResourcePool`, which has now been deprecated and will be removed in version 5.0. The `Extended` class is the default one, as it is designed to work with regular PHP requests. In order to use the `Standard` pool, you must enable it through the `ClienOptions` class:
  ```php
  $options = ClientOptions::create()
      ->withLowMemoryResourcePool();
  $client = new Client($token, $spaceId, $environmentId, $options);
  ```
  As this resource pool provides no caching (and `include` values are now skipped, for this reason), it might cause the SDK to make more API calls, so its used is discouraged for regular web applications.

## [4.0.2](https://github.com/contentful/contentful.php/tree/4.0.2) (2018-11-09)

### Fixed

* Resolving links from an entry using non-default locale caused the SDK to wrongly assume the `locale=*`. Now this is fixed, and the  entry will use either its current locale, if set, or the the `locale=*` if no locales are currently configured.

## [4.0.1](https://github.com/contentful/contentful.php/tree/4.0.1) (2018-11-08)

### Fixed

* Second parameter in `LinkResolver::resolveLinkCollection()` was changed from `string $locale = null` to `array $parameters = []`.

## [4.0.0](https://github.com/contentful/contentful.php/tree/4.0.0) (2018-11-08)

**ATTENTION**: This release contains breaking changes. Please take extra care when updating to this version. See [the upgrade guide](UPGRADE-4.0.md) for more.

### PHP Version

* Support for PHP 5.6 was dropped. The library now requires PHP 7.0 or higher, and all files use strict types, with scalar type declarations present whenever possible. As PHP 7.0 is also about to reach end of life, we strongly suggest you upgrade to at least PHP 7.1. However, this major release line of the SDK (version 4) will support PHP 7.0 regardless of its status.

### Added

* The `Client` constructor signature was changed: `public function __construct(string $accessToken, string $spaceId, string $environmentId = 'master', \Contentful\Delivery\ClientOptions $options = null)`. All options that were previously handled through the remaining parameters or the options array can now be set using the `ClientOptions` object. See the upgrade guide for a more detailed explanation. **[BREAKING]**
* The `Client` object now implements `Contentful\Delivery\Client\ClientInterface`. We encourage users to type hint against this interface rather than against the concrete implementation.
* The SDK now offers support for rich text. Check the [tutorial](https://www.contentful.com/developers/docs/php/tutorials/using-rich-text-in-the-php-cda-sdk/) on the Contentful website for more.

### Changed

* The SDK now uses version 2 of the `contentful/core` package. We encouraged users to check its [changelog](https://github.com/contentful/contentful-core.php/blob/2.0.0/CHANGELOG.md) and [upgrade guide](https://github.com/contentful/contentful-core.php/blob/2.0.0/UPGRADE-2.0.md).
* The `Query::where()` method used to accept a third parameter, which was used to specify the type of search (for instance with operators `near` or `lte`). As many users did not know this and appended the operator to the first parameter of `where()`, now the third parameter was removed. **[BREAKING]**
* The way system properties are handled was completely changed: previously the SDK used a general `SystemProperties` class with many nullable properties, now every resource type has its own system properties implementation. For instance, the calling `Contentful\Delivery\Resource\Entry::getSystemProperties()` will now return an instance of `Contentful\Delivery\SystemProperties\Entry`, which contains only the necessary methods. **[BREAKING]**
* Method `Entry::has()` when checking against a link by default checks if the link can also be resolved. This behavior can be turned off by setting the third parameter to `false`.
* Logic for resolving links was moved from the `Client` to a `LinkResolver` object. The client still provides a convenience `resolveLink()` method which forwards the call.
* Method `Client::isPreview()` was renamed `Client::isPreviewApi()`. Its opposite, `Client::isDeliveryApi()`, was also added. **[BREAKING]**
* Method `Contentful\Delivery\Mapper\BaseMapper::hydrate()` was removed. If using a custom mapper, you can use `$this->hydrator->hydrate(object|string $target, array $data)`. **[BREAKING]**
* Interface `CacheItemPoolFactoryInterface` now enforces the return type. **[BREAKING]**
* Class `InstanceRepository` was renamed `ResourcePool`, and method `Client::getInstanceRepository()` was renamed `Client::getResourcePool()`. **[BREAKING]**
* Method `ResourcePool::generateKey()` contained the `$api` parameter, which was removed as a resource pool object is supposed to operate within a single API context. **[BREAKING]**
* Third parameter of synchronization manager constructor was changed from `$preview` to `$isDeliveryApi`, which is the opposite value. However, users should never build the manager themselves, as they should access it through `Client::getSyncronizationManager()`. **[BREAKING]**

### Fixed

* Using magic method `Entry::getSomething()` with an invalid field previously resulte in an error being triggered. This behavior was fixed and now a regular `InvalidArgumentException` is thrown. **[BREAKING]**

## [3.4.0](https://github.com/contentful/contentful.php/tree/3.4.0) (2018-08-24)

### Added

* The SDK can now use a locally-cached copy of entries and asset. While this _will not_ prevent API calls when using `getEntries` or `getAssets`, it will intercept calls made with `getEntry` and `getAsset`, including those being made when resolving a link from an entry. You can enable this either using the CLI commands with the `--cache-content` flag, or passing `'cacheContent' => true` to the `$options` array in the client constructor (requires `'autoWarmup'` to also be set to true).

### Fixed

* Made sure that cache keys are always PSR-6 compliant. Certain PSR-6 implementations have more lax requirements and the SDK took advantage of that, but now all keys only use the character set defined in the official specification.

## [3.3.0](https://github.com/contentful/contentful.php/tree/3.3.0) (2018-06-18)

### Added

* Added `getSpaceId` and `getEnvironmentId` to the `Client`.

## [3.2.3](https://github.com/contentful/contentful.php/tree/3.2.3) (2018-06-15)

### Fixed

* Fixed incremental build of entries with non-default locale.

## [3.2.2](https://github.com/contentful/contentful.php/tree/3.2.2) (2018-06-06)

### Fixed

* The SDK internally keeps a registry of built resources, and for this reason, partially selecting fields in an entry might have resulted in successive API calls returning the first "incomplete" version. Now entries are continuously updated, so multiple successive API calls will update a resource to include all recently-fetched fields, plus the already existing ones.
* The SDK previously used the hyphen symbol (`-`) as a separator in cache keys. However, some more restrictive PSR-6 implementations do not allow that, so it's been changed to a dot (`.`) instead.

## [3.2.1](https://github.com/contentful/contentful.php/tree/3.2.1) (2018-06-01)

Maintenance release.

## [3.2.0](https://github.com/contentful/contentful.php/tree/3.2.0) (2018-04-27)

### Added

* Sync API now also works on non-master environments.

### Fixed

* Cache keys for entries and assets sometimes did not contain the locale part, leading to possibly duplicate requests. Now info about locales is prefetched, making sure that cache keys are always correct.

## [3.1.0](https://github.com/contentful/contentful.php/tree/3.1.0) (2018-04-26)

### Added

* Entries now have a `has($field)` method (with a magic `hasX()` equivalent) which returns whether an entry has the given field currently loaded.

### Changed

* The magic `__call` method now allow for access also using the actual field name, without `get` prefix (for instance `$entry->title()`). This was done to further improve compatibility on edge cases with templating engines such as Twig.

## [3.0.2](https://github.com/contentful/contentful.php/tree/3.0.2) (2018-04-19)

### Fixed

* Cache keys now include space ID and environment ID to better handle possible edge cases.

## [3.0.1](https://github.com/contentful/contentful.php/tree/3.0.1) (2018-04-17)

### Fixed

* Updated the SDK to use a stable version of contentful-core.

## [3.0.0](https://github.com/contentful/contentful.php/tree/3.0.0) (2018-04-16)

**ATTENTION**: This release contains breaking changes. Please take extra care when updating to this version. See [the upgrade guide](UPGRADE-3.0.md) for more.

### PHP Version

* Support for PHP 5.5 was dropped. PHP 5.5 has reached EOL in July 2016, so we highly discourage anyone from keep using it for multiple reasons, including possible security concerns. The SDK now requires at least PHP 5.6; however, PHP 5.6 is not actively supported and currently only security fixes are provided. Because of this, we strongly encourage everybody to upgrade to a version in the PHP 7 branch, ideally 7.1 or 7.2, which are actively supported. Many other popular frameworks and tools already require a version in the PHP 7 branch: Symfony 4 requires 7.1, Laravel 5.5 requires 7.0, Laravel 5.6 requires 7.1, PHPUnit 6 requires 7.0, PHPUnit 7 requires 7.1, etc. Because of the clear convergence of the PHP ecosystem towards this upgrade, and the availability of tools that allow you to easily manage your PHP version (such as Docker), we can't guarantee that the next major version of this SDK (version 4) will still keep compatibility with PHP 5.6. However, compatibility is guaranteed for the whole lifecycle of the PHP Contentful Delivery SDK version 3, and important bugfixes (including security-related ones) will be backported once version 4 will be released.

### Added

* The SDK now supports space environments.
* The `ResourceBuilder` now supports custom data type matchers so users can create their own custom resource classes.
* All resource classes now extend `Contentful\Delivery\Resource\BaseResource`, which implements `Contentful\Core\Resource\ResourceInterface`. You can use these two classes for type hinting.
* Entry objects now support accessing fields through virtual properties with `__get` (`$entry->title`) and using the `ArrayAccess` interface (`$field['title']`).

### Fixed

* There was an issue when adding a new field to a Contentful content type without purging the SDK cache, with the SDK not knowing how to build the field. Now it will create a temporary fake field of type `Unknown`, and it will also trigger a silenced error of level `E_USER_WARNING` (so users can implement some custom logic for handling the issue, without causing any problems to the actual execution). When retrieving the unknown field, it will be returned *as it was fetched from the API*, so no conversion will be made. This means that dates will be returned as strings, links will be returned as simple arrays, etc. This solution was implemented to prevent the SDK from crashing during those (hopefully) brief moments where the cache is out of sync with Contentful, and it *should not* be relied upon for actually handling the fields in a proper way.

### Changed

* The cache system has been rewritten to be made PSR-6 compatible (thanks @magnusnordlander). **[BREAKING]**
* `DynamicEntry`, `Asset`, `ContentType`, `ContentTypeField`, `Space`, and `Locale`, `DeletedAsset`, 'DeletedEntry', and `DeletedContentType` classes have been moved to a different namespace. Please check the [the upgrade guide](UPGRADE-3.0.md) for more details. **[BREAKING]**
* All parts of the SDK that were not in the `Contentful\Delivery` namespace have been moved to a separate package called [contentful-core.php](https://github.com/contentful/contentful-core.php).
* The option in the client constructor for specifying a custom URI is now called `baseUri` instead of `uriOverride`, to be more consistent with the one used in Guzzle. **[BREAKING]**
* The SDK no longer used a custom logger. It now supports any PSR-3 compatible logging implementation for permanent storage, but easy access to a log of current API requests is provides through `Client::getMessages()`, which returns an array of `Contentful\Core\Api\Message`. **[BREAKING]**
* The SDK now keeps a registry of all resources that are currently managed, called `Contentful\Delivery\InstanceRepository`. This class also wraps the PSR-6 cache pool.
* `Client::reviveJson()` is now called `Client::parseJson()` to better reflect its meaning.
* The Sync API currently only works with the `master` environment. When trying to perform sync-related operations on a client which is configured with any other environment, a `\RuntimeException` will be thrown.
* Previously, entry objects would try to use the locale fallback chain even in situations where this might lead to results that differ from those that would be normally returned from the Delivery (or Preview) API. For this reason, when requesting an entry using any other locale than `locale=*`, the locale fallback chain will not be used. The Sync API defaults to using all locales, so it's not affected. **[BREAKING]**

### Removed

* `Contentful\Delivery\EntryInterface` no longer exists. Use `Contentful\Delivery\Resource\Entry` for type hinting. **[BREAKING]**
* `Contentful\JsonHelper` was deprecated in 2.2, and has now been removed. **[BREAKING]**
* `Contentful\DateHelper` was deprecated in 2.2, and has now been removed. **[BREAKING]**
* `Contentful\File\UploadFile` was deprecated in 2.1, and has now been removed. Use `Contentful\Core\File\RemoteUploadFile` instead. **[BREAKING]**
* Resource classes have now a protected constructor, because they are not supposed to be instantiated without using the `ResourceBuilder`. **[BREAKING]**
* `Asset`, `ContentType`, `Entry`, and `DeletedResource` classes previously provided shortcut methods for accessing system properties such as revision, createdAt, etc. These shortcut methods have been removed from the main resource, but they're still accessible through a `SystemProperties` object. See the upgrade guide for more. **[BREAKING]**

## [2.4.1](https://github.com/contentful/contentful.php/tree/2.4.1) (2018-01-25)

### Added

* The `Contentful\Synchronization\Manager` class now provides a convenience method called `sync($token = null, Query $query = null)` which transparently handles a full sync, instead of having to manually call `startSync` and `continueSync`. The method returns instances of `Contentful\Synchronization\Result` wrapped in a `\Generator` object.
* Added missing exception class `Contentful\Exception\BadRequestException`.

## [2.4.0](https://github.com/contentful/contentful.php/tree/2.4.0) (2018-01-11)

### Added

* The `Contentful\Delivery\Query` class now has `linksToEntry('<entry_id>')` and `linksToAsset('<entry_id>')` methods. For users on older versions of the SDK, the same operators can be emulated by using `$query->where('links_to_entry', '<entry_id>')` and `$query->where('links_to_asset', '<asset_id>')`. `DynamicEntry` also provides a shortcut in the form `$entry->getReferences()`.

### Changed

* The SDK now implements a strict coding standard based on the one found on the [Symfony Demo](https://github.com/symfony/demo/blob/master/.php_cs.dist). The `@Symfony:risky` ruleset is currently implemented, but this could be subject to change in the event of possible incompatibilities. The style check is a mandatory part of CI.
* Methods are no longer marked as `@api` or `@internal`. If a method is public, it's assumed to be part of the API surface of the SDK, and thus any breaking change to that would require a new major version.

## [2.3.0](https://github.com/contentful/contentful.php/tree/2.3.0) (2017-12-01)

### Changed

* Dependencies to `symfony/console` and `symfony/filesystem` now also allow version `~4.0`.

## [2.2.0](https://github.com/contentful/contentful.php/tree/2.2.0) (2017-09-26)

### Changed

* `DateHelper::formatForJson()` is now deprecated and will be removed in version 3. Use `Contentful\format_date_for_json()` instead.
* `JsonHelper::encode()` and `JsonHelper::decode()` are now deprecated and will be removed in version 3. Use `GuzzleHttp\json_encode()` and `GuzzleHttp\json_decode()` instead.

### Fixed

* `LogEntry` now modifies the exception stack trace (if present) to prevent problems during serialization.

## [2.1.0](https://github.com/contentful/contentful.php/tree/2.1.0) (2017-07-14)

### Added

* Allow the exception map in `Client` to be overridden. This is done in preparation of the upcoming CMA SDK.
* The third parameter `$options` of the `Client::request()` method now accepts an optional value with key `baseUri`. This is in preparation for the CMA SDK.
* Revamped the [reference documentation](https://contentful.github.io/contentful.php/api/) to be based on Sami and to include previous versions of the SDK.
* `LocalUploadFile` now handles asset files which have been uploaded to `upload.contentful.com` but have yet to be processed. This fixes a possible edge case, and it's also done in preparation for the upcoming CMA SDK.
* `Contentful\Client` now includes a `getLogger` method, for easy access to the logger currently in use.

### Fixed

* Slight fixes to error messages in exceptions thrown in the `ResourceBuilder`.
* Adds a missing exception message in `SynchronizationManager::continueSync()`.

## [2.0.1](https://github.com/contentful/contentful.php/tree/2.0.1) (2017-06-16)

### Fixed

* `ResourceBuilder` now correctly handles locale when building referenced assets.
* `Asset::getFile` now uses fallback chain logic to determine the locale to use.

## [2.0.0](https://github.com/contentful/contentful.php/tree/2.0.0) (2017-06-13)

**ATTENTION**: This release contains breaking changes. Please take extra care when updating to this version.

### Added

* `Link` implements the `JsonSerializable` interface. This is done in preparation for the upcoming CMA SDK.
* `UploadFile` class now manages files which aren't yet processed (for Preview API) **[BREAKING]**. `Contentful\Delivery\Asset::getFile` now returns `Contentful\Delivery\File\FileInterface` instead of one of `File|ImageFile`. If you were type hinting on either one of those, please now use the interface or add `UploadFile` to the possible types.
* Exceptions thrown because of an API error now extend `ApiException`. This class gives access to some additional data like, the request, response and request ID.
* Extended `Client` to support a future CMA SDK.

### Fixed

* Retrieving a list of entries that contained multiple loops creates too many objects. **[BREAKING]** ([#105](https://github.com/contentful/contentful.php/pull/105))
  The new behavior is, that any entry that appears multiple times in the graph of the response will be the same instance.
* The `contentful` script used to warm up/clear the cache was not marked as a binary in `composer.json` and thus not published to `vendor/bin`.
* In console commands `<info>` can't be used as part of an Exception message. ([#129](https://github.com/contentful/contentful.php/pull/129))
* Assets that are part of includes would not be resolved and always fetched again.
* `Client::request` ignored the timer returned in `LoggerInterface::getTimer` when timing requests.

### Changed

* Moved file classes to a sub-namespace `Contentful\File` **[BREAKING]**.
  * `Contentful\File` to `Contentful\File\File`
  * `Contentful\ImageFile` to `Contentful\File\ImageFile`
  * `Contentful\ImageOptions` to `Contentful\File\ImageOptions`

## [1.2.0](https://github.com/contentful/contentful.php/tree/1.2.0) (2017-05-16)

### Added

* Implemented `ResourceArray::getItems` to allow access to the values of a `ResourceArray` as an actual PHP array.
* Send the new `X-Contentful-User-Agent` header.

## [1.1.0](https://github.com/contentful/contentful.php/tree/1.1.0) (2017-05-11)

### Added

* Implemented `DeletedEntry::getContentType()` to be used with webhooks. ([#101](https://github.com/contentful/contentful.php/pull/101))

### Changed

* The minimum required version of `guzzlehttp/psr7` is now 1.4.

### Fixed

* Retrieving assets with the Preview API fails if no file is set. ([#99](https://github.com/contentful/contentful.php/pull/99))
* When lazy-loading a linked entry, it would always be fetched in the default locale. ([#109](https://github.com/contentful/contentful.php/pull/109))

## [1.0.0](https://github.com/contentful/contentful.php/tree/1.0.0) (2017-04-26)

### Added

* Content in disabled fields can now be read.

## [0.8.1-beta](https://github.com/contentful/contentful.php/tree/0.8.0-beta) (2017-04-11)

### Fixed

* The caching of resolved links does not work for an array of links.

## [0.8.0-beta](https://github.com/contentful/contentful.php/tree/0.8.0-beta) (2017-04-10)

**ATTENTION**: This release contains breaking changes. Please take extra care when updating to this version.

### Changed

* Renamed a few classes to move them outside the Delivery namespace. **[BREAKING]**
  * `Contentful\Delivery\Link` to `Contentful\Link`
  * `Contentful\Delivery\ImageOptions` to `Contentful\ImageOptions`
  * `Contentful\Delivery\File` to `Contentful\File`
  * `Contentful\Delivery\ImageFile` to `Contentful\ImageFile`
* Renamed `ResourceNotFoundException` to `NotFoundException` to match the names the API uses. **[BREAKING]**
* Turned `Contentful\Query` into an abstract class to promote separation between CDA and CMA SDKs. **[BREAKING]**

### Removed

* Removed all get* methods except `getQueryData()` and `getQueryString()` from the various query classes. **[BREAKING]**

### Fixed

* The `FilesystemCache` would try to read cached content types from the wrong file name.
* `CacheWarmer` wrote incorrect data for content types.
* Retrieving a cached content type would cause the maximum function nesting level to be exceeded.
* Correctly set the `Accept` header for API versioning. Previously the `Content-Type` header was set instead.
* Serializing a `LogEntry` would fail if no response has been set.

## [0.7.0-beta](https://github.com/contentful/contentful.php/tree/0.7.0-beta) (2017-04-06)

**ATTENTION**: This release contains breaking changes. Please take extra care when updating to this version.

### Added

* Added support for the `webp` format in the Images API.
* Introduced `RateLimitExceededException`, `InvalidQueryException` and `AccessTokenInvalidException` for more specific error handling. **[BREAKING]**
* Allow injecting a custom Guzzle instance into `Client`.
* Allow fetching content in a single locale by adding the locale code to the query. **[BREAKING]**
  **MIGRATION:** To retain the old behavior set the default locale to `'*''` when creating the client. This could look
  like: `new Client($token, $spaceID, false, null, ['defaultLocale => '*'])`
* Allow setting the locale in which you work when creating the client.
* Allow overriding the URI used to connect with the Contentful API.
* The `select` operator can now be specified on queries. Thanks @Haehnchen.
* Support for the `all` operator and passing arrays as `$value` in `Query::where()`.
* Support for ordering by multiple fields.
* The space metadata and the content types can now be cached with a CLI command.
* Support for caching the Space and Content Types. The cache has to be manually warmed and cleared.

### Changed

* Changed the behavior of getting an array of links to not throw an exception when one of them has been deleted from the space. ([#19](https://github.com/contentful/contentful.php/pull/19))
* Removed the caching of `Asset` and `Entry` instances. **[BREAKING]**
* Changed the internal data format from object to array. This should make no observable difference to the public API.
* Moved all Exception classes to their own namespace. **[BREAKING]**
* Changed the signature of the constructor of `Contentful\Delivery\Client`. Several options are now in an options array. **[BREAKING]**
* The Sync API can now also be used with the Preview API. Only initial syncs are supported.
* Dist zip files no longer include the tests directory. If you need them use `composer install --prefer-source`.

### Removed

* Dropped `BearerToken` to make it easier to inject custom Guzzle instances. Thanks @Haehnchen. **[BREAKING]**
* The class generator has been removed. It was unusable.

### Fixed

* Assets that have no title would throw an uncaught exception.
* Handling of missing values for a locale in Assets. Solved by implementing fallback locales for Assets too. ([#38](https://github.com/contentful/contentful.php/issues/38))
* Fields that have the literal value `null` are now treated like they don't exist. Previously they might have causes a
fatal error. **Note:** This does not 100% match the behaviour of the Contentful API.
* The error message for `Query::setLimit` was incorrect.
* Allow accessing fields where the first letter of the ID is capitalized. ([#68](https://github.com/contentful/contentful.php/pull/68))

## [0.6.5-beta](https://github.com/contentful/contentful.php/tree/0.6.5-beta) (2016-09-10)

### Added

* Added gzip compression for API requests.

### Changed

* Raised the minimum Guzzle version to 6.2.1.
  This version addressed the HTTP_PROXY security vulnerability (CVE-2016-5385).

### Fixed

* Trying to retrieve fields that end with "Id" fails. [#9](https://github.com/contentful/contentful.php/issues/9)

## [0.6.4-beta](https://github.com/contentful/contentful.php/tree/0.6.4-beta) (2016-03-03)

### Added

* Made LogEntry implement Serializable.

### Changed

* Send the correct Content-Type header for API versioning.

## [0.6.3-beta](https://github.com/contentful/contentful.php/tree/0.6.3-beta) (2016-03-02)

### Added

* Implemented missing functionality of the Contentful Images API in ImageOptions.
* Added the missing method LogEntry::getResponse
* Added LogEntry::isError

### Fixed

* Logged requests were always shown as belonging to the Delivery API, even when the Preview API was used.
* Fields not present in an Entry would lead to an error.
* The Synchronization Manager's method `startSync` was type hinted to the wrong Query class.
* API responses were not correctly logged.

## [0.6.2-beta](https://github.com/contentful/contentful.php/tree/0.6.2-beta) (2016-02-22)

### Added

* Compatibility with Symfony 3.
* The ability to log information about requests made against the Contentful API.

### Changed

* Use PSR-7 internally.

### Fixed

* Default headers were never actually sent

## [0.6.1-beta](https://github.com/contentful/contentful.php/tree/0.6.1-beta) (2016-01-19)

### Added

* Send a User-Agent header with API requests.

### Changed

* When GitHub is generating archives, are few files with metadata are excluded.

### Fixed

* Calling the get*Id Method on a field that is not a link or an array of links did not cause an error. ([#2](https://github.com/contentful/contentful.php/pull/2))
* Accessing a non-localized field would fail with and throw a PHP notice.

## [0.6.0-beta](https://github.com/contentful/contentful.php/tree/0.6.0-beta) (2015-12-11)

Initial release

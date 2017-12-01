# Changelog

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

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

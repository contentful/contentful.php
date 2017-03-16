# Changelog

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

**ATTENTION**: This release contains breaking changes. Please take extra care when updating to this version.

### Added
* Added support for the `webp` format in the Images API.
* Introduced `RateLimitExceededException` for more specific error handling. **[BREAKING]**
* Allow injecting a custom Guzzle instance into `Client`.
* Allow fetching content in a single locale by adding the locale code to the query. **[BREAKING]**
  **MIGRATION:** To retain the old behavior set the default locale to `'*''` when creating the client. This could look
  like: `new Client($token, $spaceID, false, null, ['defaultLocale => '*'])`
* Allow setting the locale in which you work when creating the client.
* Allow overriding the URI used to connect with the Contentful API.
* The `select` operator can now be specified on queries.
* Introduced `InvalidQueryException` for more specific error handling. **[BREAKING]**
* Introduced `AccessTokenInvalidException` for more specific error handling. **[BREAKING]**

### Changed
* Changed the behavior of getting an array of links to not throw an exception when one of them has been deleted from the space. ([#19](https://github.com/contentful/contentful.php/pull/19))
* Removed the caching of `Asset` and `Entry` instances. **[BREAKING]**
* Changed the internal data format from object to array. This should make no observable difference to the public API.
* Moved all Exception classes to their own namespace. **[BREAKING]**
* Changed the signature of the constructor of `Contentful\Delivery\Client`. Several options are now in an options array. **[BREAKING]**

### Removed
* Dropped `BearerToken` to make it easier to inject custom Guzzle instances. **[BREAKING]**

### Fixed
* Assets that have no title would throw an uncaught exception.
* Handling of missing values for a locale in Assets. Solved by implementing fallback locales for Assets too.
* Fields that have the literal value `null` are now treated like they don't exist. Previously they might have causes a
fatal error. **Note:** This does not 100% match the behaviour of the Contentful API.
* The error message for `Query::setLimit` was incorrect.

## [0.6.5-beta](https://github.com/contentful/contentful.php/tree/0.6.5-beta) (2016-09-10)

### Added
* Added gzip compression for API requests.

### Changed
* Raised the minimum Guzzle version to 6.2.1.
  This version addressed the HTTP_PROXY security vulnerability (CVE-2016-5385).

### Fixed
* Fix [#9](https://github.com/contentful/contentful.php/issues/9) Trying to retrieve fields that end with "Id" fails.

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
* When GitHub is generating archieves, are few files with metadata are excluded.

### Fixed
* Calling the get*Id Method on a field that is not a link or an array of links did not cause an error. (#2, originally reported by @andrewevansmith)
* Accessing a non-localized field would fail with and throw a PHP notice.

## [0.6.0-beta](https://github.com/contentful/contentful.php/tree/0.6.0-beta) (2015-12-11)

Initial release

[Unreleased]: https://github.com/contentful/contentful.php/compare/0.6.4-beta...HEAD

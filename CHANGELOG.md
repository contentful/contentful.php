# Changelog

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

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

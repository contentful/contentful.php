![PHP](.github/header-php.png)


<p align="center">
  <a href="https://www.contentful.com/slack/">
    <img src="https://img.shields.io/badge/-Join%20Community%20Slack-2AB27B.svg?logo=slack&maxAge=31557600" alt="Join Contentful Community Slack" />
  </a>
  &nbsp;
  <a href="https://www.contentfulcommunity.com/">
    <img src="https://img.shields.io/badge/-Join%20Community%20Forum-3AB2E6.svg?logo=data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA1MiA1OSI+CiAgPHBhdGggZmlsbD0iI0Y4RTQxOCIgZD0iTTE4IDQxYTE2IDE2IDAgMCAxIDAtMjMgNiA2IDAgMCAwLTktOSAyOSAyOSAwIDAgMCAwIDQxIDYgNiAwIDEgMCA5LTkiIG1hc2s9InVybCgjYikiLz4KICA8cGF0aCBmaWxsPSIjNTZBRUQyIiBkPSJNMTggMThhMTYgMTYgMCAwIDEgMjMgMCA2IDYgMCAxIDAgOS05QTI5IDI5IDAgMCAwIDkgOWE2IDYgMCAwIDAgOSA5Ii8+CiAgPHBhdGggZmlsbD0iI0UwNTM0RSIgZD0iTTQxIDQxYTE2IDE2IDAgMCAxLTIzIDAgNiA2IDAgMSAwLTkgOSAyOSAyOSAwIDAgMCA0MSAwIDYgNiAwIDAgMC05LTkiLz4KICA8cGF0aCBmaWxsPSIjMUQ3OEE0IiBkPSJNMTggMThhNiA2IDAgMSAxLTktOSA2IDYgMCAwIDEgOSA5Ii8+CiAgPHBhdGggZmlsbD0iI0JFNDMzQiIgZD0iTTE4IDUwYTYgNiAwIDEgMS05LTkgNiA2IDAgMCAxIDkgOSIvPgo8L3N2Zz4K&maxAge=31557600" alt="Join Contentful Community Forum" />
  </a>
</p>

# contentful.php — Contentful PHP Delivery Library


[![Packagist](https://img.shields.io/packagist/v/contentful/contentful.svg?style=for-the-badge)](https://packagist.org/packages/contentful/contentful)
[![PHP version](https://img.shields.io/packagist/php-v/contentful/contentful.svg?style=for-the-badge)](https://packagist.org/packages/contentful/contentful)
[![Packagist](https://img.shields.io/github/license/contentful/contentful.php.svg?style=for-the-badge)](https://packagist.org/packages/contentful/contentful)
[![CircleCI](https://circleci.com/gh/contentful/contentful.php.svg?style=shield)](https://circleci.com/gh/contentful/contentful.php)


> PHP library for the Contentful [Content Delivery API](https://www.contentful.com/developers/docs/references/content-delivery-api/) and [Content Preview API](https://www.contentful.com/developers/docs/references/content-preview-api/). It helps you to easily access your Content stored in Contentful with your PHP applications.

## What is Contentful?

[Contentful](https://www.contentful.com/) provides content infrastructure for digital teams to power websites, apps, and devices. Unlike a CMS, Contentful was built to integrate with the modern software stack. It offers a central hub for structured content, powerful management and delivery APIs, and a customizable web app that enable developers and content creators to ship their products faster.

<details>
<summary>Table of contents</summary>

- [contentful.php — Contentful PHP Delivery library](#contentfulphp-contentful-php-delivery-library)
  - [What is Contentful?](#what-is-contentful)
  - [Core Features](#core-features)
  - [Getting started](#getting-started)
  - [Installation](#installation)
  - [Your first request](#your-first-request)
  - [Using this library with the Preview API](#using-this-library-with-the-preview-api)
  - [Authentication](#authentication)
  - [Documentation & References](#documentation-references)
  - [Configuration](#configuration)
  - [Reference documentation](#reference-documentation)
  - [Tutorials & other resources](#tutorials-other-resources)
  - [Upgrade](#upgrade)
  - [Reach out to us](#reach-out-to-us)
  - [You have questions about how to use this library?](#you-have-questions-about-how-to-use-this-library)
  - [You found a bug or want to propose a feature?](#you-found-a-bug-or-want-to-propose-a-feature)
  - [You need to share confidential information or have other questions?](#you-need-to-share-confidential-information-or-have-other-questions)
  - [Get involved](#get-involved)
  - [License](#license)
  - [Code of Conduct](#code-of-conduct)

</details>

## Core Features

- Content retrieval through the [Content Delivery API](https://www.contentful.com/developers/docs/references/content-delivery-api/) and [Content Preview API](https://www.contentful.com/developers/docs/references/content-preview-api/).
- [Synchronization](https://www.contentful.com/developers/docs/concepts/sync/)
- [Localization support](https://www.contentful.com/developers/docs/concepts/locales/)
- [Link resolution](https://www.contentful.com/developers/docs/concepts/links/)

## Getting started

In order to get started with the Contentful PHP library you'll need not only to install it, but also to get credentials which will allow you to have access to your content in Contentful. This package requires PHP 7.2 or higher or PHP 8.0 or higher.

### Installation

Install the library using [Composer](https://getcomposer.org/):

``` bash
composer require contentful/contentful
```

### Your first request

The following code snippet is the most basic one you can use to get some content from Contentful with this library:
All interactions with the library go through `Contentful\Delivery\Client`. To create a new client an access token and a space ID have to be passed to the constructor.

``` php
$client = new \Contentful\Delivery\Client(
    'b4c0n73n7fu1', # This is the access token for this space. Normally you get both ID and the token in the Contentful web app
    'cfexampleapi' # This is the space ID. A space is like a project folder in Contentful terms
);

try {
    $entry = $client->getEntry('nyancat');
} catch (\Contentful\Core\Exception\NotFoundException $exception) {
    // Entry does not exist
}
```

### Using this library with the Preview API

This library can also be used with the Preview API. In order to do so, you need to use the Preview API access token, available on the same page where you get the Delivery API token, and tell the client to use the different API:

``` php
$options = \Contentful\Delivery\ClientOptions::create()
    ->usingPreviewApi();
$client = new \Contentful\Delivery\Client($accessToken, $spaceId, $environmentId, $options);
```

You can find all available methods of our client in our [reference documentation](https://contentful.github.io/contentful.php).

### Authentication

To get your own content from Contentful, an app should authenticate with an OAuth bearer token.

You can create API keys using the [Contentful web interface](https://app.contentful.com/). Go to the app, open the space that you want to access (top left corner lists all the spaces), and navigate to the APIs area. Open the API Keys section and create your first token. Done.

Don't forget to also get your Space ID.

For more information, check the [Contentful REST API reference on Authentication](https://www.contentful.com/developers/docs/references/authentication/).

## Documentation & References

- [Configuration](#configuration)
- [Reference documentation](#reference-documentation)
- [Tutorials & other resources](#tutorials--other-resources)
- [Troubleshooting](#troubleshooting)
- [Advanced Concepts](#advanced-concepts)

### Configuration

The `ClientOptions` class allows you to configure the client in a variety of different ways:

``` php
$options = \Contentful\Delivery\ClientOptions::create()
    ->usingPreviewApi()
    ->withDefaultLocale(string $defaultLocale = null)
    ->withHost(string $host)
    ->withLogger(Psr\Log\LoggerInterface $logger)
    ->withCache(Psr\Cache\CacheItemPoolInterface $cache, bool $autoWarmup = false, bool $cacheContent = false)
    ->withHttpClient(GuzzleHttp\Client $client)
    ->withoutMessageLogging()
    ->withQueryCache(Psr\Cache\CacheItemPoolInterface $queryCacheItemPool, int $queryCacheLifetime = 0)
;

$client = new \Contentful\Delivery\Client(
    string $accessToken,
    string $spaceId,
    string $environmentId = 'master',
    ClientOptions $options = null
);
```

| Client parameter | Default    | Description |
| ---------------- | ---------- | ----------- |
| `$accessToken`   |            | **Required**. Your access token |
| `$spaceId`       |            | **Required**. Your space ID |
| `$environmentId` | `'master'` | Your environment ID |
| `$options`       | `null`     | A `ClientOptions` object |

| ClientOptions method | Parameters | Description |
| -------------------- | ---------- | ----------- |
| `usingPreviewApi()` | - | Use the Preview API host (`preview.contentful.com`) |
| `withDefaultLocale()` | `string $locale` | Set a locale to be automatically used for all requests |
| `withHost()` | `string $host` | A string to override the default Contentful API URL, useful if you have a proxy between your application and the Contentful API |
| `withLogger()` | `Psr\Log\LoggerInterface $logger` | A PSR-3 logger. Two types of logs are written: a generic one using either the `INFO` or `ERROR` level (depending on the response status code) with a brief summary, and a complete dump of request and response using the `DEBUG` level. We suggest to configure the logger minimum level according to your needs. |
| `withCache()` | `Psr\Cache\CacheItemPoolInterface $cache` | A PSR-6 cache item pool. This will be used to stored data such as content types and locales, which are always needed but don't change often |
| `withCache()` | `bool $autoWarmup = false` | When using a cache pool, set this to true to automatically fill the cache during regular use |
| `withCache()` | `bool $cacheContent = false` | When using a cache pool with `$autoWarmup` set to true, se this to true to fill the cache with entries and assets during runtime. This may speed up execution when calling `$client->getEntry($entryId)` and `$client->getAsset($assetId)`, but *not* when calling the `getEntries()` and `getAssets()` methods, as the client can't reliably know which entries or assets will be returned by the API, and for this reason the cache can't intercept the call. |
| `withHttpClient()` | `GuzzleHttp\Client $client` | A Guzzle client instance, which can be configured with custom middleware |
| `withoutMessageLogging()` | - | Do not store API requests and responses (which can use a lot of memory). If messages are not stored, they will not be retrievable from `Client::getMessages()` for debugging and inspection purposes |
| `withQueryCache()` | `Psr\Cache\CacheItemPoolInterface $queryCacheItemPool` | A PSR-6 cache item pool. This will be used to cache items retrieved with queries when calling `$client->getEntries($query)`. |
| `withQueryCache()` | `int $queryCacheLifetime = 0` | The number of seconds of lifetime for `$client->getEntries($query)` cache items. There's no invalidation mechanism on these cache items so consider to set a low lifetime (for example 60 seconds). |

### Reference documentation

The [PHP library reference](https://contentful.github.io/contentful.php) documents what objects and methods are exposed by this library, what arguments they expect and what kind of data is returned.

Most methods also have examples which show you how to use them.

### Tutorials & other resources

* This library is a wrapper around our Contentful Delivery REST API. Some more specific details such as search parameters and pagination are better explained on the [REST API reference](https://www.contentful.com/developers/docs/references/content-delivery-api/), and you can also get a better understanding of how the requests look under the hood.
* Check the [Contentful for PHP](https://www.contentful.com/developers/docs/php/) page for Tutorials, Demo Apps, and more information on using PHP with Contentful.

### Upgrade

For details about how to upgrade from version 3.x to version 4, please check the [changelog entry for version 4.0.0](CHANGELOG.md#400-2018-11-08) and the [upgrade to version 4 guide](UPGRADE-4.0.md).

For details about how to upgrade from version 2.x to version 3, please check the [changelog entry for version 3.0.0](CHANGELOG.md#300-2018-04-16) and the [upgrade to version 3 guide](UPGRADE-3.0.md).

## Reach out to us

### You have questions about how to use this library?

* Reach out to our community forum: [![Contentful Community Forum](https://img.shields.io/badge/-Join%20Community%20Forum-3AB2E6.svg?logo=data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA1MiA1OSI+CiAgPHBhdGggZmlsbD0iI0Y4RTQxOCIgZD0iTTE4IDQxYTE2IDE2IDAgMCAxIDAtMjMgNiA2IDAgMCAwLTktOSAyOSAyOSAwIDAgMCAwIDQxIDYgNiAwIDEgMCA5LTkiIG1hc2s9InVybCgjYikiLz4KICA8cGF0aCBmaWxsPSIjNTZBRUQyIiBkPSJNMTggMThhMTYgMTYgMCAwIDEgMjMgMCA2IDYgMCAxIDAgOS05QTI5IDI5IDAgMCAwIDkgOWE2IDYgMCAwIDAgOSA5Ii8+CiAgPHBhdGggZmlsbD0iI0UwNTM0RSIgZD0iTTQxIDQxYTE2IDE2IDAgMCAxLTIzIDAgNiA2IDAgMSAwLTkgOSAyOSAyOSAwIDAgMCA0MSAwIDYgNiAwIDAgMC05LTkiLz4KICA8cGF0aCBmaWxsPSIjMUQ3OEE0IiBkPSJNMTggMThhNiA2IDAgMSAxLTktOSA2IDYgMCAwIDEgOSA5Ii8+CiAgPHBhdGggZmlsbD0iI0JFNDMzQiIgZD0iTTE4IDUwYTYgNiAwIDEgMS05LTkgNiA2IDAgMCAxIDkgOSIvPgo8L3N2Zz4K&maxAge=31557600)](https://support.contentful.com/)
* Jump into our community slack channel: [![Contentful Community Slack](https://img.shields.io/badge/-Join%20Community%20Slack-2AB27B.svg?logo=slack&maxAge=31557600)](https://www.contentful.com/slack/)

### You found a bug or want to propose a feature?

* File an issue here on GitHub: [![File an issue](https://img.shields.io/badge/-Create%20Issue-6cc644.svg?logo=github&maxAge=31557600)](https://github.com/contentful/contentful.php/issues/new). Make sure to remove any credential from your code before sharing it.

### You need to share confidential information or have other questions?

* File a support ticket at our Contentful Customer Support: [![File support ticket](https://img.shields.io/badge/-Submit%20Support%20Ticket-3AB2E6.svg?logo=data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA1MiA1OSI+CiAgPHBhdGggZmlsbD0iI0Y4RTQxOCIgZD0iTTE4IDQxYTE2IDE2IDAgMCAxIDAtMjMgNiA2IDAgMCAwLTktOSAyOSAyOSAwIDAgMCAwIDQxIDYgNiAwIDEgMCA5LTkiIG1hc2s9InVybCgjYikiLz4KICA8cGF0aCBmaWxsPSIjNTZBRUQyIiBkPSJNMTggMThhMTYgMTYgMCAwIDEgMjMgMCA2IDYgMCAxIDAgOS05QTI5IDI5IDAgMCAwIDkgOWE2IDYgMCAwIDAgOSA5Ii8+CiAgPHBhdGggZmlsbD0iI0UwNTM0RSIgZD0iTTQxIDQxYTE2IDE2IDAgMCAxLTIzIDAgNiA2IDAgMSAwLTkgOSAyOSAyOSAwIDAgMCA0MSAwIDYgNiAwIDAgMC05LTkiLz4KICA8cGF0aCBmaWxsPSIjMUQ3OEE0IiBkPSJNMTggMThhNiA2IDAgMSAxLTktOSA2IDYgMCAwIDEgOSA5Ii8+CiAgPHBhdGggZmlsbD0iI0JFNDMzQiIgZD0iTTE4IDUwYTYgNiAwIDEgMS05LTkgNiA2IDAgMCAxIDkgOSIvPgo8L3N2Zz4K&maxAge=31557600)](https://www.contentful.com/support/)

## Get involved

[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?maxAge=31557600)](http://makeapullrequest.com)

**Important**: Right now, the API has `php-vcr` as a development dependency, which does not officially support PHP8 yet. If you want to develop on PHP8, you will need to install the dependencies with `composer install --ignore-platform-reqs` to overwrite this requirement.

## License

This repository is published under the [MIT](LICENSE) license.

## Code of Conduct

We want to provide a safe, inclusive, welcoming, and harassment-free space and experience for all participants, regardless of gender identity and expression, sexual orientation, disability, physical appearance, socioeconomic status, body size, ethnicity, nationality, level of experience, age, religion (or lack thereof), or other identity markers.

[Read our full Code of Conduct](https://github.com/contentful-developer-relations/community-code-of-conduct).

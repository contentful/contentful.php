# contentful.php

[![Packagist](https://img.shields.io/packagist/v/contentful/contentful.svg?style=flat-square)](https://packagist.org/packages/contentful/contentful.php)
[![Travis](https://img.shields.io/travis/contentful/contentful.php.svg?style=flat-square)](https://travis-ci.org/contentful/contentful.php)
[![Packagist](https://img.shields.io/github/license/contentful/contentful.php.svg?style=flat-square)](https://packagist.org/packages/contentful/contentful.php)
[![Code Climate](https://img.shields.io/codeclimate/github/contentful/contentful.php.svg?style=flat-square)](https://codeclimate.com/github/contentful/contentful.php)
[![Codecov](https://img.shields.io/codecov/c/github/contentful/contentful.php.svg?style=flat-square)](https://codecov.io/gh/contentful/contentful.php)

PHP SDK for [Contentful](https://www.contentful.com)'s Content Delivery API. The SDK requires PHP 5.6 or above, PHP 7 is supported.

## What is Contentful?

[Contentful](https://www.contentful.com) provides a content infrastructure for digital teams to power content in websites, apps, and devices. Unlike a CMS, Contentful was built to integrate with the modern software stack. It offers a central hub for structured content, powerful management and delivery APIs, and a customizable web app that enable developers and content creators to ship digital products faster.

## Setup

To add this package to your `composer.json` and install it execute the following command:

``` bash
composer require contentful/contentful
```

Then, if not already done, include the Composer autoloader:

``` php
<?php

require_once 'vendor/autoload.php';
```

## Usage

All interactions with the SDK go through `Contentful\Delivery\Client`. To create a new client an access token and a space ID have to be passed to the constructor.

``` php
$client = new \Contentful\Delivery\Client('access-token', 'space-id', 'environment-id');
```

To fetch an `Entry` just call the method `getEntry()` with the ID of the desired entry.

``` php
$entry = $client->getEntry('entry-id');
```

The fields of an entry can than be accessed through getter methods.

``` php
$entry->getId(); // 'entry-id'
```

More than one Entry can be fetched by calling `getEntries()`. This methods takes an optional `Contentful\Delivery\Query` object, which allows filtering and sorting results.

``` php
$query = (new \Contentful\Delivery\Query())
    ->where('sys.updatedAt', new \DateTime('2013-01-01'));

$entries = $client->getEntries($query);
```

### Default Ordering

Bear in mind that there is no default ordering included for any method which returns a `Contentful\Core\Resource\ResourceArray` instance. This means that if you plan to page through more than 100 results with multiple requests, there is no guarantee that you will cover all entries. It is however possible to specify custom ordering:

``` php
$query = (new \Contentful\Delivery\Query())
    ->orderBy('sys.createdAt', true);

$entries = $client->getEntries($query);
```

The above snippet will fetch all entries, ordered by newest-to-oldest.

### Preview Mode

The Content Delivery API only returns published entries. However, you might want to preview content in your app before making it public for your users. For this, you can use the Preview API, which will return _all_ entries, regardless of their published status. To do so, just pass `true` as the fourth argument to the `Client` constructor.

``` php
$client = new \Contentful\Delivery\Client('access-token', 'space-id', 'environment-id', true);
```

Apart from the configuration option, you can use the SDK without modifications with one exception: you need to obtain a preview access token, which you can get in the "API" tab of the Contentful web  app. In preview mode, data can be invalid, because no validation is performed on unpublished entries, so tour app needs to take that into account.

### Default Locale

When working with localized content it can be tedious to specify the locale on every request. Alternatively, a locale can be specified on the client constructor. This value then overrides the space's default locale. To retrieve all content in Italian, the code would look like this:

``` php
$client = new \Contentful\Delivery\Client('access-token', 'space-id', 'environment-id', false, 'it-IT');
```

## Documentation

* [SDK API Reference](https://contentful.github.io/contentful.php/api/)
* Check the [Contentful for PHP](https://www.contentful.com/developers/docs/php/) page for tutorials, example apps, and more information on other ways of using PHP with Contentful
* [CDA REST API reference](https://www.contentful.com/developers/docs/references/content-delivery-api/) for additional details on the Delivery API

## License

Copyright (c) 2015-2018 Contentful GmbH. Code released under the MIT license. See [LICENSE](LICENSE) for further details.

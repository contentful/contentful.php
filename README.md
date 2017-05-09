contentful.php
===============

[![Build Status](https://travis-ci.org/contentful/contentful.php.svg?branch=master)](https://travis-ci.org/contentful/contentful.php)
[![codecov](https://codecov.io/gh/contentful/contentful.php/branch/master/graph/badge.svg)](https://codecov.io/gh/contentful/contentful.php)

PHP SDK for [Contentful's][1] Content Delivery API.

[Contentful][1] is a content management platform for web applications, mobile apps and connected devices. It allows you to create, edit & manage content in the cloud and publish it anywhere via powerful API. Contentful offers tools for managing editorial teams and enabling cooperation between organizations.

The SDK requires at least PHP 5.5.9. PHP 7 is supported.

Setup
=====

To add this package to your `composer.json` and install it execute the following command:

```bash
php composer.phar require contentful/contentful
````

Then, if not already done, include the Composer autoloader:

```php
require_once 'vendor/autoload.php';
```

Usage
=====

All interactions with the SDK go trough `Contentful\Delivery\Client`. To create a new client an access token and a space ID have to be passed to the constructor.

```php
$client = new \Contentful\Delivery\Client('access-token', 'space-id');
```

To fetch an Entry just call the method `getEntry()` with the ID of the desired entry.

```php
$entry = $client->getEntry('entry-id');
```

The fields of an entry can than be accessed through getter methods.

```php
$entry->getId(); // 'entry-id'
```

More than one Entry can be fetched by calling `getEntries()`. This methods requires a `Contentful\Delivery\Query` object, which allows filtering and sorting results.

```php
$query = new \Contentful\Delivery\Query;
$query->where('sys.updatedAt', new \DateTime('2013-01-01'));
$entries = $client->getEntries($query);
```

### Default Ordering

Bear in mind that there is no default ordering included for any method which returns a `Contentful\ResourceArray` instance. This means that if you plan to page through more than 100 results with multiple requests, there is no guarantee that you will cover all entries. It is however possible to specify custom ordering:

```php
$query = new \Contentful\Delivery\Query;
$query->orderBy('sys.createdAt', true);
$entries = $client->getEntries($query);
```

The above snippet will fetch all Entries, ordered by newest-to-oldest.

### Preview Mode

The Content Delivery API only returns published Entries. However, you might want to preview content in your app before making it public for your users. For this, you can use the preview mode, which will return **all** Entries, regardless of their published status. To do so, just pass `true` as the third argument to the `Client` constructor.

```php
$client = new \Contentful\Delivery\Client('access-token', 'space-id', true);
```

Apart from the configuration option, you can use the SDK without modifications with one exception: you need to obtain a preview access token, which you can get in the "API" tab of the Contentful app. In preview mode, data can be invalid, because no validation is performed on unpublished entries. Your app needs to deal with that. Be aware that the access token is read-write and should in no case be shipped with a production app.

### Default Locale

When working with localized content it can be tedious to specify the locale on every request. Alternatively, a locale can be specified on the client constructor. This value then overrides the space's default locale. To retrieve all content in German, the code would look like this:

```php
$client = new \Contentful\Delivery\Client('access-token', 'space-id', false, 'de-DE');
```

Documentation
=============

* [SDK API Reference][3]
* Check the [Contentful for PHP][4] page for tutorials, example apps, and more information on other ways of using PHP with Contentful
* [CDA REST API reference][5] for additional details on the Delivery API

License
=======

Copyright (c) 2015-2017 Contentful GmbH. Code released under the MIT license. See [LICENSE][2] for further details.

 [1]: https://www.contentful.com
 [2]: LICENSE
 [3]: https://contentful.github.io/contentful.php/api/
 [4]: https://www.contentful.com/developers/docs/php/
 [5]: https://www.contentful.com/developers/docs/references/content-delivery-api/

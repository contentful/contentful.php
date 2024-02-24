<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

use VCR\Request;
use VCR\VCR;

date_default_timezone_set('UTC');

if ('code-coverage' === getenv('CONTENTFUL_SDK_ENV')) {
    return;
}

/**
 * @return array
 */
function clean_headers_array(Request $request)
{
    // @todo This can be done much more nicely with PHP 5.6 and ARRAY_FILTER_USE_BOTH
    $headers = array_filter($request->getHeaders());

    foreach ($headers as $name => $value) {
        $lcName = mb_strtolower($name);
        if ('user-agent' === $lcName || 'x-contentful-user-agent' === $lcName) {
            unset($headers[$name]);
        }
    }

    return $headers;
}

// The VCR needs to be loaded before the Client is loaded for the first time or it will fail
VCR::configure()
    ->setMode('once')
//    ->setMode('new_episodes')
    ->setStorage('json')
    ->enableLibraryHooks(['stream_wrapper', 'curl'])
    ->setCassettePath('tests/Recordings')
    ->addRequestMatcher('custom_headers', function (Request $first, Request $second) {
        $first = clean_headers_array($first);
        $second = clean_headers_array($second);

        return $first === $second;
    })
    ->enableRequestMatchers(['method', 'url', 'query_string', 'host', 'body', 'post_fields', 'custom_headers'])
;

VCR::turnOn();
VCR::turnOff();

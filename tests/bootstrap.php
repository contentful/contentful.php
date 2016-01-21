<?php

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * @param \VCR\Request $request
 *
 * @return array
 */
function clean_headers_array(\VCR\Request $request) {
    // @todo This can be done much more nicely with PHP 5.6 and ARRAY_FILTER_USE_BOTH
    $headers = array_filter($request->getHeaders());

    foreach ($headers as $name => $value) {
        if (strtolower($name) === 'user-agent') {
            unset($headers[$name]);
        }
    }

    return $headers;
}

// The VCR needs to be loaded before the Client is loaded for the first time or it will fail
\VCR\VCR::configure()
    ->setMode('once')
    ->setStorage('json')
    ->addRequestMatcher('custom_headers', function(\VCR\Request $first, \VCR\Request $second) {
        $first = clean_headers_array($first);
        $second = clean_headers_array($second);

        return $first == $second;
    })
    ->enableRequestMatchers(['method', 'url', 'query_string', 'host', 'body', 'post_fields', 'custom_headers']);;

\VCR\VCR::turnOn();
\VCR\VCR::turnOff();


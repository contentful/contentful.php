<?php

// This is the API docs generator configuration.
// To generate docs, execute:
// php sami.phar update sami.php
//
// For more about Sami, visit
// https://github.com/FriendsOfPHP/Sami

use Sami\Sami;
use Sami\Version\GitVersionCollection;
use Symfony\Component\Finder\Finder;

$dir = __DIR__ . '/src';

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in($dir)
;

// generate documentation for all v2.0.* tags, the 2.0 branch, and the master one
$versions = GitVersionCollection::create($dir)
    ->addFromTags('*')
;

return new Sami($iterator, [
    'title'     => 'Contentful CDA SDK for PHP',
    'versions'  => $versions,
    'build_dir' => __DIR__ . '/build/docs/%version%',
    'cache_dir' => __DIR__ . '/build/doc_cache/%version%',
]);

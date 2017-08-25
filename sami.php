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

$versions = GitVersionCollection::create($dir)
    ->addFromTags('*')
    ->add('master', 'Master branch')
;

return new Sami($iterator, [
    'title'     => 'Contentful CDA SDK for PHP',
    'versions'  => $versions,
    'build_dir' => __DIR__ . '/build/docs/api/%version%',
    'cache_dir' => __DIR__ . '/build/doc_cache/%version%',
]);

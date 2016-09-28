<?php

use Symfony\CS\Config\Config;
use Symfony\CS\Finder\DefaultFinder;
use Symfony\CS\FixerInterface;

$finder = DefaultFinder::create()
    ->in('src');

return Config::create()
    ->finder($finder)
    ->level(FixerInterface::PSR2_LEVEL)
    ->setUsingCache(true);

<?php

$config = require __DIR__.'/vendor/contentful/core/scripts/php-cs-fixer.php';

return $config(
    'contentful',
    true,
    ['bin', 'extra', 'scripts', 'src', 'tests']
);

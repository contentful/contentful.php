<?php

$config = require __DIR__.'/scripts/php-cs-fixer.php';

return $config(
    'contentful',
    true,
    ['bin', 'extra', 'scripts', 'src', 'tests']
);

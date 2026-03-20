#!/usr/bin/env bash
set -euo pipefail

temp_composer="composer.devcontainer.json"
temp_lock="composer.devcontainer.lock"

cleanup() {
  rm -f "$temp_composer" "$temp_lock"
}

trap cleanup EXIT

php <<'PHP'
<?php

$composer = json_decode(file_get_contents('composer.json'), true, 512, JSON_THROW_ON_ERROR);
unset($composer['require-dev']['roave/backward-compatibility-check']);

file_put_contents(
    'composer.devcontainer.json',
    json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL
);
PHP

COMPOSER="$temp_composer" composer install -n --prefer-dist

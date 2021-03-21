#!/usr/bin/env bash
wget -O sami.phar https://github.com/contentful/contentful-core.php/raw/master/scripts/sami.phar
php sami.phar update sami.php
git checkout -qf FETCH_HEAD
php scripts/create-redirector.php build/docs/api/index.html
php scripts/create-redirector.php build/docs/index.html

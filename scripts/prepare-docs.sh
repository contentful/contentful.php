#!/usr/bin/env bash
git clone https://${GH_REPO} full
cd full
git checkout ${TRAVIS_TAG}
php ../sami.phar update sami.php
git checkout ${TRAVIS_TAG}
mkdir build/gh-pages
mv build/docs build/gh-pages/api
php scripts/create-redirector.php build/gh-pages/api/index.html
php scripts/create-redirector.php build/gh-pages/index.html

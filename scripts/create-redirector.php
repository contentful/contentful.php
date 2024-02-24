<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

$travisRepoSlug = getenv('TRAVIS_REPO_SLUG');
$indexFile = $argv[1];

$shellOutput = shell_exec('git tag');
$tags = explode("\n", $shellOutput);
$tags = array_filter($tags, function ($tag) {
    return '' !== trim($tag);
});

// We remove all non-stable versions from the list as we don't want to direct the docs to them by default
$tags = array_filter($tags, function ($tag) {
    return false === mb_strpos($tag, '-');
});

usort($tags, function ($a, $b) {
    return version_compare($b, $a);
});

$tags[] = 'master';

$newestTag = $tags[0];
$repoParts = explode('/', $travisRepoSlug);
$repoOwner = $repoParts[0];
$repoName = $repoParts[1];

$html = '<meta http-equiv="refresh" content="0; url=https://'.$repoOwner.'.github.io/'.$repoName.'/api/'.$newestTag.'" />';

file_put_contents($indexFile, $html);

echo 'Created index file redirecting to '.$newestTag.'.'."\n";

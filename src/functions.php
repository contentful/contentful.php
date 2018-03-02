<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

/**
 * @param string $api     A string representation of the API in use
 * @param string $spaceId
 *
 * @return string
 */
function cache_key_space($api, $spaceId)
{
    return \sprintf('contentful-%s-space-%s', $api, $spaceId);
}

/**
 * @param string $api           A string representation of the API in use
 * @param string $contentTypeId
 *
 * @return string
 */
function cache_key_content_type($api, $contentTypeId)
{
    return \sprintf('contentful-%s-contentType-%s', $api, $contentTypeId);
}

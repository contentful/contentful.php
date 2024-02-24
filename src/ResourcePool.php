<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery;

use Contentful\Delivery\ResourcePool\Extended;

/**
 * ResourcePool class.
 *
 * This class acts as a registry for current objects managed by the Client.
 * It also abstracts access to objects stored in cache.
 *
 * @deprecated 4.1 Use Contentful\Delivery\ResourcePool\Extended instead
 */
class ResourcePool extends Extended
{
}

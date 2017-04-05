<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Cache;

interface CacheInterface
{
    public function readSpace();

    public function readContentType($id);
}

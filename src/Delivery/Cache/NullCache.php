<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Cache;

class NullCache implements CacheInterface
{
    public function readSpace()
    {
        return null;
    }

    /**
     * @param string $id
     */
    public function readContentType($id)
    {
        return null;
    }
}

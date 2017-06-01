<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Cache;

class NullCache implements CacheInterface
{
    /**
     * @return null
     */
    public function readSpace()
    {
    }

    /**
     * @param string $id
     *
     * @return null
     */
    public function readContentType($id)
    {
    }
}

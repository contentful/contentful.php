<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Cache;

interface CacheInterface
{
    /**
     * @return string|null
     */
    public function readSpace();

    /**
     * @param  string $id
     *
     * @return string|null
     */
    public function readContentType($id);
}

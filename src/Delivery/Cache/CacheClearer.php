<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Cache;

use Symfony\Component\Filesystem\Filesystem;

class CacheClearer
{
    /**
     * @var string
     */
    private $spaceId;

    /**
     * CacheClearer constructor.
     *
     * @param  string $spaceId ID of the space for which the cache should be cleared
     */
    public function __construct($spaceId)
    {
        $this->spaceId = $spaceId;
    }

    /**
     * @param  string $cacheDir
     */
    public function clear($cacheDir)
    {
        $spacePath = $cacheDir . '/' . $this->spaceId;
        $fs = new Filesystem();

        $fs->remove($spacePath);
    }
}

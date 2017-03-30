<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Cache;

use Symfony\Component\Filesystem\Filesystem;

class CacheClearer
{
    private $spaceId;

    public function __construct($spaceId)
    {
        $this->spaceId = $spaceId;
    }

    public function clear($cacheDir)
    {
        $spacePath = $cacheDir . '/' . $this->spaceId;
        $fs = new Filesystem();

        $fs->remove($spacePath);
    }
}

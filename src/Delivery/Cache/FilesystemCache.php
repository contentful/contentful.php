<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Cache;

use Symfony\Component\Filesystem\Filesystem;

class FilesystemCache implements CacheInterface
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * FilesystemCache constructor.
     *
     * @param  string $cacheDir
     * @param  string $spaceId
     */
    public function __construct($cacheDir, $spaceId)
    {
        $this->fs = new Filesystem;
        $this->cacheDir = $cacheDir . '/' . $spaceId;
    }

    /**
     * @return string|null
     */
    public function readSpace()
    {
        $path = $this->cacheDir . '/space.json';

        if (!$this->fs->exists($path)) {
            return null;
        }

        return file_get_contents($path);
    }

    /**
     * @param  string $id
     *
     * @return string|null
     */
    public function readContentType($id)
    {
        $path = $this->cacheDir . '/ct-' . $id . '.json';
        if (!$this->fs->exists($path)) {
            return null;
        }

        return file_get_contents($path);
    }
}

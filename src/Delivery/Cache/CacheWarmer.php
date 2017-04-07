<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Cache;

use Contentful\Delivery\Client;
use Contentful\Delivery\Query;
use Symfony\Component\Filesystem\Filesystem;

class CacheWarmer
{
    /**
     * @var Client
     */
    private $client;

    /**
     * CacheWarmer constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $cacheDir
     */
    public function warmUp($cacheDir)
    {
        $fs = new Filesystem();

        $space = $this->client->getSpace();

        $query = (new Query())
            ->setLimit(100);

        $contentTypes = $this->client->getContentTypes($query);
        $spacePath = $cacheDir . '/' . $space->getId();

        if (!$fs->exists($spacePath)) {
            $fs->mkdir($spacePath);
        }

        $fs->dumpFile($spacePath . '/space.json', json_encode($space));

        foreach ($contentTypes as $contentType) {
            $fs->dumpFile($spacePath . '/ct-' . $contentType->getId() . '.json', json_encode($contentType));
        }
    }
}

<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

use Cache\Adapter\Void\VoidCachePool;
use Contentful\Client as BaseClient;
use Contentful\Delivery\Cache\InstanceCache;
use Contentful\Delivery\Synchronization\Manager;
use Contentful\Link;
use Contentful\ResourceArray;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * A Client is used to communicate the Contentful Delivery API.
 *
 * A Client is only responsible for one Space. When access to multiple spaces is required, create multiple Clients.
 *
 * This class can be configured to use the Preview API instead of the Delivery API. This grants access to not yet published content.
 */
class Client extends BaseClient
{
    /**
     * @var string
     */
    const VERSION = '3.0.0-dev';

    /**
     * @var string
     */
    const API_DELIVERY = 'DELIVERY';

    /**
     * @var string
     */
    const API_PREVIEW = 'PREVIEW';

    /**
     * @var ResourceBuilder
     */
    private $builder;

    /**
     * @var InstanceCache
     */
    private $instanceCache;

    /**
     * @var bool
     */
    private $preview;

    /**
     * @var string|null
     */
    private $defaultLocale;

    /**
     * @var CacheItemPoolInterface
     */
    private $cacheItemPool;

    /**
     * @var bool
     */
    private $autoWarmup;

    /**
     * @var string
     */
    private $spaceId;

    /**
     * Client constructor.
     *
     * @param string      $token         Delivery API Access Token for the space used with this Client
     * @param string      $spaceId       ID of the space used with this Client
     * @param bool        $preview       true to use the Preview API
     * @param string|null $defaultLocale The default is to fetch the Space's default locale. Set to a locale
     *                                   string, e.g. "en-US" to fetch content in that locale. Set it to "*"
     *                                   to fetch content in all locales.
     * @param array       $options       An array of optional configuration options. The following keys are possible:
     *                                   * guzzle      Override the guzzle instance used by the Contentful client
     *                                   * logger      Inject a Contentful logger
     *                                   * uriOverride Override the uri that is used to connect to the Contentful API (e.g. 'https://cdn.contentful.com/'). The trailing slash is required.
     *                                   * cache       Null or a PSR-6 cache item pool. The client only writes to the cache if autoWarmup is true, otherwise, you are responsible for warming it up using \Contentful\Delivery\Cache\CacheWarmer.
     *                                   * autoWarmup  Warm up the cache automatically
     */
    public function __construct($token, $spaceId, $preview = false, $defaultLocale = null, array $options = [])
    {
        $baseUri = $preview ? 'https://preview.contentful.com/' : 'https://cdn.contentful.com/';
        $api = $preview ? self::API_PREVIEW : self::API_DELIVERY;

        $options = \array_replace([
            'guzzle' => null,
            'logger' => null,
            'uriOverride' => null,
            'cacheDir' => null,
            'cache' => null,
            'autoWarmup' => false,
        ], $options);

        $guzzle = $options['guzzle'];
        $logger = $options['logger'];
        $uriOverride = $options['uriOverride'];
        $this->autoWarmup = $options['autoWarmup'];

        if (null !== $uriOverride) {
            $baseUri = $uriOverride;
        }
        $baseUri .= 'spaces/';

        parent::__construct($token, $baseUri.$spaceId.'/', $api, $logger, $guzzle);

        $this->preview = $preview;
        $this->instanceCache = new InstanceCache();
        $this->defaultLocale = $defaultLocale;
        $this->spaceId = $spaceId;

        $this->cacheItemPool = $options['cache'] ?: new VoidCachePool();

        if (!$this->cacheItemPool instanceof CacheItemPoolInterface) {
            throw new \InvalidArgumentException('The cache parameter must be a PSR-6 cache item pool or null.');
        }

        $this->builder = new ResourceBuilder($this, $this->instanceCache, $this->cacheItemPool, $spaceId);
    }

    /**
     * {@inheritdoc}
     */
    public function getApi()
    {
        return $this->isPreview() ? self::API_PREVIEW : self::API_DELIVERY;
    }

    /**
     * The name of the library to be used in the User-Agent header.
     *
     * @return string
     */
    protected function getSdkName()
    {
        return 'contentful.php';
    }

    /**
     * The version of the library to be used in the User-Agent header.
     *
     * @return string
     */
    protected function getSdkVersion()
    {
        return self::VERSION;
    }

    /**
     * Returns the Content-Type (MIME-Type) to be used when communication with the API.
     *
     * @return string
     */
    protected function getApiContentType()
    {
        return 'application/vnd.contentful.delivery.v1+json';
    }

    /**
     * @param string      $assetId
     * @param string|null $locale
     *
     * @return Asset
     */
    public function getAsset($assetId, $locale = null)
    {
        $locale = null === $locale ? $this->defaultLocale : $locale;

        return $this->requestAndBuild('GET', 'assets/'.$assetId, [
            'query' => ['locale' => $locale],
        ]);
    }

    /**
     * @param Query|null $query
     *
     * @return ResourceArray
     */
    public function getAssets(Query $query = null)
    {
        $query = null !== $query ? $query : new Query();
        $queryData = $query->getQueryData();
        if (!isset($queryData['locale'])) {
            $queryData['locale'] = $this->defaultLocale;
        }

        return $this->requestAndBuild('GET', 'assets', [
            'query' => $queryData,
        ]);
    }

    /**
     * @param string $contentTypeId
     *
     * @return ContentType
     */
    public function getContentType($contentTypeId)
    {
        if ($this->instanceCache->hasContentType($contentTypeId)) {
            return $this->instanceCache->getContentType($contentTypeId);
        }

        $key = \Contentful\cache_key_content_type($this->getApi(), $contentTypeId);
        $cacheItem = $this->cacheItemPool->getItem($key);
        if ($cacheItem->isHit()) {
            return $this->reviveJson($cacheItem->get());
        }

        return $this->requestAndBuild('GET', 'content_types/'.$contentTypeId, [], $cacheItem);
    }

    /**
     * @param Query|null $query
     *
     * @return ResourceArray
     */
    public function getContentTypes(Query $query = null)
    {
        $query = null !== $query ? $query : new Query();

        return $this->requestAndBuild('GET', 'content_types', [
            'query' => $query->getQueryData(),
        ]);
    }

    /**
     * @param string      $entryId
     * @param string|null $locale
     *
     * @return Entry
     */
    public function getEntry($entryId, $locale = null)
    {
        $locale = null === $locale ? $this->defaultLocale : $locale;

        return $this->requestAndBuild('GET', 'entries/'.$entryId, [
            'query' => ['locale' => $locale],
        ]);
    }

    /**
     * @param Query|null $query
     *
     * @return ResourceArray
     */
    public function getEntries(Query $query = null)
    {
        $query = null !== $query ? $query : new Query();
        $queryData = $query->getQueryData();
        if (!isset($queryData['locale'])) {
            $queryData['locale'] = $this->defaultLocale;
        }

        return $this->requestAndBuild('GET', 'entries', [
            'query' => $queryData,
        ]);
    }

    /**
     * @return Space
     */
    public function getSpace()
    {
        if ($this->instanceCache->hasSpace()) {
            return $this->instanceCache->getSpace();
        }

        $key = \Contentful\cache_key_space($this->getApi(), $this->spaceId);
        $cacheItem = $this->cacheItemPool->getItem($key);
        if ($cacheItem->isHit()) {
            return $this->reviveJson($cacheItem->get());
        }

        return $this->requestAndBuild('GET', '', [], $cacheItem);
    }

    /**
     * Resolve a link to it's resource.
     *
     * @param Link        $link
     * @param string|null $locale
     *
     * @throws \InvalidArgumentException when encountering an unexpected link type
     *
     * @return Asset|Entry
     */
    public function resolveLink(Link $link, $locale = null)
    {
        $id = $link->getId();
        $type = $link->getLinkType();

        switch ($link->getLinkType()) {
            case 'Entry':
                return $this->getEntry($id, $locale);
            case 'Asset':
                return $this->getAsset($id, $locale);
            default:
                throw new \InvalidArgumentException('Tyring to resolve link for unknown type "'.$type.'".');
        }
    }

    /**
     * Revive JSON previously cached.
     *
     * @param string $json
     *
     * @throws \Contentful\Exception\SpaceMismatchException When attempting to revive JSON belonging to a different space
     *
     * @return Asset|ContentType|Entry|Space|Synchronization\DeletedAsset|Synchronization\DeletedContentType|Synchronization\DeletedEntry|\Contentful\ResourceArray
     */
    public function reviveJson($json)
    {
        $data = \GuzzleHttp\json_decode($json, true);

        return $this->builder->buildObjectsFromRawData($data);
    }

    /**
     * Internal method for \Contentful\Delivery\Synchronization\Manager.
     *
     * @param array $queryData
     *
     * @return mixed
     *
     * @see \Contentful\Delivery\Synchronization\Manager
     */
    public function syncRequest(array $queryData)
    {
        return $this->request('GET', 'sync', [
            'query' => $queryData,
        ]);
    }

    /**
     * Returns true when using the Preview API.
     *
     * @return bool
     *
     * @see https://www.contentful.com/developers/docs/references/content-preview-api/#/reference Preview API Reference
     */
    public function isPreview()
    {
        return $this->preview;
    }

    /**
     * Get an instance of the synchronization manager. Note that with the Preview API only an inital sync
     * is giving valid results.
     *
     * @return Manager
     *
     * @see https://www.contentful.com/developers/docs/concepts/sync/ Sync API
     */
    public function getSynchronizationManager()
    {
        return new Manager($this, $this->builder, $this->preview);
    }

    /**
     * @param string $method
     * @param string $path
     * @param array  $options
     *
     * @return Asset|ContentType|Entry|Space|Synchronization\DeletedAsset|Synchronization\DeletedContentType|Synchronization\DeletedEntry|\Contentful\ResourceArray
     */
    private function requestAndBuild($method, $path, array $options = [], CacheItemInterface $cacheItem = null)
    {
        $rawData = $this->request($method, $path, $options);

        if ($cacheItem && $this->autoWarmup) {
            $cacheItem->set(\json_encode($rawData));
            $this->cacheItemPool->save($cacheItem);
        }

        return $this->builder->buildObjectsFromRawData($rawData);
    }
}

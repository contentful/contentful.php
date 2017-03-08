<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use Contentful\Client as BaseClient;
use Contentful\Delivery\Synchronization\Manager;
use Contentful\Query as BaseQuery;
use Contentful\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * A Client is used to communicate the Contentful Delivery API.
 *
 * A Client is only responsible for one Space. When access to multiple spaces is required, create multiple Clients.
 *
 * This class can be configured to use the Preview API instead of the Delivery API. This grants access to not yet published content.
 *
 * @api
 */
class Client extends BaseClient
{
    const VERSION = '0.7.0-dev';

    /**
     * @var ResourceBuilder
     */
    private $builder;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var string
     */
    private $spaceId;

    /**
     * @var bool
     */
    private $preview;

    /**
     * @var string|null
     */
    private $defaultLocale;

    /**
     * Client constructor.
     *
     * @param string $token Delivery API Access Token for the space used with this Client
     * @param string $spaceId ID of the space used with this Client.
     * @param bool $preview True to use the Preview API.
     * @param CacheInterface $cache
     * @param LoggerInterface $logger
     * @param GuzzleClientInterface $guzzle
     * @param string|null $defaultLocale The default is to fetch the Space's default locale. Set to a locale
     *                                             string, e.g. "en-US" to fetch content in that locale. Set it to "*"
     *                                             to fetch content in all locales.
     *
     * @api
     */
    public function __construct(
        $token,
        $spaceId,
        $preview = false,
        CacheInterface $cache = null,
        LoggerInterface $logger = null,
        GuzzleClientInterface $guzzle = null,
        $defaultLocale = null
    ) {
        $this->spaceId = $spaceId;
        $baseUri = $preview ? 'https://preview.contentful.com/spaces/' : 'https://cdn.contentful.com/spaces/';
        $api = $preview ? 'PREVIEW' : 'DELIVERY';

        parent::__construct($token, $baseUri . $this->spaceId . '/', $api, $logger, $guzzle);

        $this->preview = $preview;

        $this->cache = $cache ?: new InstanceCache();
        $this->builder = new ResourceBuilder($this, $this->spaceId);
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * The name of the library to be used in the User-Agent header.
     *
     * @return string
     */
    protected function getUserAgentAppName()
    {
        return 'ContentfulCDA/' . self::VERSION;
    }

    /**
     * @param  string $id
     * @param  string|null $locale
     *
     * @return Asset
     *
     * @api
     */
    public function getAsset($id, $locale = null)
    {
        $locale = $locale === null ? $this->defaultLocale : $locale;

        $cacheKeyInfo = array($id, $locale);
        $cacheKey = $this->getCacheKey('Asset', $cacheKeyInfo);
        $asset = $this->cache->get($cacheKey, null);

        if ($asset !== null && $asset instanceof Asset) {
            return $asset;
        }

        $asset = $this->requestAndBuild('GET', 'assets/' . $id, [
            'query' => ['locale' => $locale]
        ]);

        if ($asset !== null && $asset instanceof Asset) {
            $this->cache->set($cacheKey, $asset);
        }

        return $asset;
    }

    /**
     * @param  BaseQuery|null $query
     *
     * @return \Contentful\ResourceArray
     *
     * @api
     */
    public function getAssets(BaseQuery $query = null)
    {
        $query = $query !== null ? $query : new BaseQuery;
        $queryData = $query->getQueryData();
        if (!isset($queryData['locale'])) {
            $queryData['locale'] = $this->defaultLocale;
        }

        $queryKey = $this->getCacheKey('Asset', $queryData);
        $queries = $this->cache->get('queries', null);
        if (is_array($queries) && array_key_exists($queryKey, $queries)) {
            return $queries[$queryKey];
        }

        $queryResult =  $this->requestAndBuild('GET', 'assets', [
            'query' => $queryData
        ]);

        if (!is_array($queries)) {
            $queries = array();
        }

        $queries[$queryKey] = $queryResult;
        $this->cache->set('queries', $queries);
        return $queryResult;
    }

    /**
     * @param  string $id
     *
     * @return ContentType
     *
     * @api
     */
    public function getContentType($id)
    {
        $cacheKey = $this->getCacheKey('ContentType', array($id));
        $contentType = $this->cache->get($cacheKey, null);

        if ($contentType !== null && $contentType instanceof ContentType) {
            return $contentType;
        }

        $contentType = $this->requestAndBuild('GET', 'content_types/' . $id);

        if ($contentType !== null && $contentType instanceof ContentType) {
            $this->cache->set($cacheKey, $contentType);
        }

        return $contentType;
    }

    /**
     * @param  BaseQuery|null $query
     *
     * @return \Contentful\ResourceArray
     *
     * @api
     */
    public function getContentTypes(BaseQuery $query = null)
    {
        $query = $query !== null ? $query : new BaseQuery;

        $queryKey = $this->getCacheKey('ContentType', array());
        $queries = $this->cache->get('queries', null);
        if (is_array($queries) && array_key_exists($queryKey, $queries)) {
            return $queries[$queryKey];
        }

        $queryResult = $this->requestAndBuild('GET', 'content_types', [
            'query' => $query->getQueryData()
        ]);

        if (!is_array($queries)) {
            $queries = array();
        }

        $queries[$queryKey] = $queryResult;
        $this->cache->set('queries', $queries);
        return $queryResult;
    }

    /**
     * @param  string $id
     * @param  string|null $locale
     *
     * @return EntryInterface
     *
     * @api
     */
    public function getEntry($id, $locale = null)
    {
        $locale = $locale === null ? $this->defaultLocale : $locale;

        $cacheKeyInfo = array($id, $locale);
        $cacheKey = $this->getCacheKey('Entry', $cacheKeyInfo);
        $entry = $this->cache->get($cacheKey, null);

        if ($entry !== null && $entry instanceof EntryInterface) {
            return $entry;
        }

        $entry = $this->requestAndBuild('GET', 'entries/' . $id, [
            'query' => ['locale' => $locale]
        ]);

        if ($entry !== null && $entry instanceof EntryInterface) {
            $this->cache->set($cacheKey, $entry);
        }

        return $entry;
    }

    /**
     * @param  BaseQuery $query
     *
     * @return \Contentful\ResourceArray
     *
     * @api
     */
    public function getEntries(BaseQuery $query = null)
    {
        $query = $query !== null ? $query : new BaseQuery;
        $queryData = $query->getQueryData();
        if (!isset($queryData['locale'])) {
            $queryData['locale'] = $this->defaultLocale;
        }

        $queryKey = $this->getCacheKey('Entry', array());
        $queries = $this->cache->get('queries', array());
        if (array_key_exists($queryKey, $queries)) {
            return $queries[$queryKey];
        }

        $queryResult = $this->requestAndBuild('GET', 'entries', [
            'query' => $queryData
        ]);

        $queries[$queryKey] = $queryResult;
        $this->cache->set('queries', $queries);
        return $queryResult;
    }

    /**
     * @return \Contentful\Delivery\Space
     *
     * @api
     */
    public function getSpace()
    {
        $cacheKey = $this->getCacheKey('Space', array($this->spaceId));
        $space = $this->cache->get($cacheKey, null);

        if ($space !== null && $space instanceof Space) {
            return $space;
        }

        $space = $this->requestAndBuild('GET', '');

        if ($space !== null && $space instanceof Space) {
            $this->cache->set($cacheKey, $space);
        }

        return $space;
    }

    /**
     * Resolve a link to it's resource.
     *
     * @param Link $link
     *
     * @return Asset|EntryInterface
     *
     * @throws \InvalidArgumentException When encountering an unexpected link type.
     *
     * @internal
     */
    public function resolveLink(Link $link)
    {
        $id = $link->getId();
        $type = $link->getLinkType();

        switch ($link->getLinkType()) {
            case 'Entry':
                return $this->getEntry($id);
            case 'Asset':
                return $this->getAsset($id);
            default:
                throw new \InvalidArgumentException('Tyring to resolve link for unknown type "' . $type . '".');
        }
    }

    /**
     * Revive JSON previously cached.
     *
     * @param  string $json
     *
     * @return Asset|ContentType|DynamicEntry|Space|Synchronization\DeletedAsset|Synchronization\DeletedEntry|\Contentful\ResourceArray
     *
     * @throws SpaceMismatchException When attempting to revive JSON belonging to a different space
     *
     * @api
     */
    public function reviveJson($json)
    {
        $data = $this->decodeJson($json);
        $result = $this->builder->buildObjectsFromRawData($data);

        switch (get_class($result)) {
            case Space::class:
                $cacheKey = $this->getCacheKey('Space', array($result->getId()));
                break;
            case ContentType::class:
                $cacheKey = $this->getCacheKey('ContentType', array($result->getId()));
                break;
            case Asset::class:
                $cacheKey = $this->getCacheKey('Asset', array($result->getId()));
                break;
            case DynamicEntry::class:
                $cacheKey = $this->getCacheKey('Entry', array($result->getId()));
                break;
            default:
                $cacheKey = '';
        }

        if ($cacheKey !== '') {
            $this->cache->set($cacheKey, $result);
        }

        return $result;
    }

    /**
     * Internal method for \Contentful\Delivery\Synchronization\Manager
     *
     * @param  array $queryData
     *
     * @return mixed
     *
     * @see \Contentful\Delivery\Synchronization\Manager
     * @internal
     */
    public function syncRequest(array $queryData)
    {
        return $this->request('GET', 'sync', [
            'query' => $queryData
        ]);
    }

    /**
     * Returns true when using the Preview API
     *
     * @return bool
     *
     * @see https://www.contentful.com/developers/docs/references/content-preview-api/#/reference Preview API Reference
     * @api
     */
    public function isPreview()
    {
        return $this->preview;
    }

    /**
     * Get an instance of the synchronization manager.
     *
     * @return Manager
     *
     * @throws \RuntimeException If this method is called while using the Preview API.
     *
     * @see https://www.contentful.com/developers/docs/concepts/sync/ Sync API
     * @api
     */
    public function getSynchronizationManager()
    {
        if ($this->preview) {
            throw new \RuntimeException('SynchronizationManager is not available for the Preview API.');
        }

        return new Manager($this, $this->builder);
    }

    private function requestAndBuild($method, $path, array $options = [])
    {
        return $this->builder->buildObjectsFromRawData($this->request($method, $path, $options));
    }

    /**
     * Retrieve cache key
     *
     * @param string $sysType
     * @param array $cacheKeyInfo
     * @return string
     */
    private function getCacheKey($sysType, array $cacheKeyInfo)
    {
        if ($sysType !== '') {
            $cacheKeyInfo[] = $sysType;
        }

        $cacheKey = implode('|', $cacheKeyInfo);
        $cacheKey = sha1($cacheKey);

        return $cacheKey;
    }
}

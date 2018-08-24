<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

use Cache\Adapter\Void\VoidCachePool;
use Contentful\Core\Api\BaseClient;
use Contentful\Core\Api\Link;
use Contentful\Core\Resource\ResourceArray;
use Contentful\Core\Resource\ResourceInterface;
use Contentful\Delivery\Resource\Asset;
use Contentful\Delivery\Resource\ContentType;
use Contentful\Delivery\Resource\Entry;
use Contentful\Delivery\Resource\Environment;
use Contentful\Delivery\Resource\Space;
use Contentful\Delivery\Synchronization\Manager;
use Psr\Cache\CacheItemPoolInterface;

/**
 * A Client is used to communicate the Contentful Delivery API.
 *
 * A Client is only responsible for one space and one environment.
 * When access to multiple spaces or environments is required, create multiple Clients.
 *
 * This class can be configured to use the Preview API instead of the Delivery API.
 * This grants access to not yet published content.
 */
class Client extends BaseClient
{
    /**
     * @var string
     */
    const VERSION = '3.5.0-dev';

    /**
     * @var string
     */
    const API_DELIVERY = 'DELIVERY';

    /**
     * @var string
     */
    const API_PREVIEW = 'PREVIEW';

    /**
     * The URI for the Delivery API.
     *
     * @var string
     */
    const URI_DELIVERY = 'https://cdn.contentful.com';

    /**
     * The URI for the Preview API.
     *
     * @var string
     */
    const URI_PREVIEW = 'https://preview.contentful.com';

    /**
     * @var ResourceBuilder
     */
    private $builder;

    /**
     * @var InstanceRepository
     */
    private $instanceRepository;

    /**
     * @var bool
     */
    private $preview;

    /**
     * @var string|null
     */
    private $defaultLocale;

    /**
     * @var string
     */
    private $spaceId;

    /**
     * @var string
     */
    private $environmentId;

    /**
     * @var ScopedJsonDecoder
     */
    private $scopedJsonDecoder;

    /**
     * Client constructor.
     *
     * @param string      $token         Delivery API Access Token for the space used with this Client
     * @param string      $spaceId       ID of the space used with this Client
     * @param string      $environmentId ID of the environment used with this Client
     * @param bool        $preview       true to use the Preview API
     * @param string|null $defaultLocale The default is to fetch the Space's default locale. Set to a locale
     *                                   string, e.g. "en-US" to fetch content in that locale. Set it to "*"
     *                                   to fetch content in all locales.
     * @param array       $options       An array of optional configuration. The following options are available:
     *                                   * guzzle       Override the guzzle instance used by the Contentful client
     *                                   * logger       A PSR-3 logger
     *                                   * baseUri      Override the uri that is used to connect to the Contentful API (e.g. 'https://cdn.contentful.com/').
     *                                   * cache        Null or a PSR-6 cache item pool. The client only writes to the cache if autoWarmup is true, otherwise, you are responsible for warming it up using \Contentful\Delivery\Cache\CacheWarmer.
     *                                   * autoWarmup   Warm up the cache automatically for content types and locales
     *                                   * cacheContent Warm up the cache automatically for entries and assets (requires autoWarmup to also be set to true)
     */
    public function __construct($token, $spaceId, $environmentId = 'master', $preview = false, $defaultLocale = null, array $options = [])
    {
        $options = \array_replace([
            'guzzle' => null,
            'logger' => null,
            'baseUri' => null,
            'cache' => null,
            'autoWarmup' => false,
            'cacheContent' => false,
        ], $options);

        $baseUri = $preview ? self::URI_PREVIEW : self::URI_DELIVERY;
        if (null !== $options['baseUri']) {
            $baseUri = $options['baseUri'];

            if ('/' === \mb_substr($baseUri, -1)) {
                $baseUri = \mb_substr($baseUri, 0, -1);
            }
        }

        parent::__construct($token, $baseUri, $options['logger'], $options['guzzle']);

        $this->preview = $preview;
        $this->defaultLocale = $defaultLocale;
        $this->spaceId = $spaceId;
        $this->environmentId = $environmentId;

        $cacheItemPool = $options['cache'] ?: new VoidCachePool();
        if (!$cacheItemPool instanceof CacheItemPoolInterface) {
            throw new \InvalidArgumentException('The cache parameter must be a PSR-6 cache item pool or null.');
        }

        $this->instanceRepository = new InstanceRepository(
            $this,
            $cacheItemPool,
            (bool) $options['autoWarmup'],
            $this->spaceId,
            $this->environmentId,
            (bool) $options['cacheContent']
        );
        $this->builder = new ResourceBuilder($this, $this->instanceRepository);
        $this->scopedJsonDecoder = new ScopedJsonDecoder($this->spaceId, $this->environmentId);
    }

    /**
     * {@inheritdoc}
     */
    public function getApi()
    {
        return $this->isPreview() ? self::API_PREVIEW : self::API_DELIVERY;
    }

    /**
     * @return string
     */
    public function getSpaceId()
    {
        return $this->spaceId;
    }

    /**
     * @return string
     */
    public function getEnvironmentId()
    {
        return $this->environmentId;
    }

    /**
     * @return ResourceBuilder
     */
    public function getResourceBuilder()
    {
        return $this->builder;
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
     * Returns the instance repository currently in use.
     *
     * @return InstanceRepository
     */
    public function getInstanceRepository()
    {
        return $this->instanceRepository;
    }

    /**
     * Returns the locale to be used in a cache key.
     *
     * @param string|null $locale
     *
     * @return string
     */
    private function getLocaleForCacheKey($locale)
    {
        if ($locale) {
            return $locale;
        }

        return $this->getEnvironment()->getDefaultLocale()->getCode();
    }

    /**
     * @param string      $assetId
     * @param string|null $locale
     *
     * @return Asset
     */
    public function getAsset($assetId, $locale = null)
    {
        $locale = $locale ?: $this->defaultLocale;

        return $this->requestAndBuild(
            '/spaces/'.$this->spaceId.'/environments/'.$this->environmentId.'/assets/'.$assetId,
            ['locale' => $locale],
            'Asset',
            $assetId,
            $this->getLocaleForCacheKey($locale)
        );
    }

    /**
     * @param Query|null $query
     *
     * @return ResourceArray
     */
    public function getAssets(Query $query = null)
    {
        $queryData = $query ? $query->getQueryData() : [];
        if (!isset($queryData['locale'])) {
            $queryData['locale'] = $this->defaultLocale;
        }

        return $this->requestAndBuild(
            '/spaces/'.$this->spaceId.'/environments/'.$this->environmentId.'/assets',
            $queryData
        );
    }

    /**
     * @param string $contentTypeId
     *
     * @return ContentType
     */
    public function getContentType($contentTypeId)
    {
        return $this->requestAndBuild(
            '/spaces/'.$this->spaceId.'/environments/'.$this->environmentId.'/content_types/'.$contentTypeId,
            [],
            'ContentType',
            $contentTypeId
        );
    }

    /**
     * @param Query|null $query
     *
     * @return ResourceArray
     */
    public function getContentTypes(Query $query = null)
    {
        return $this->requestAndBuild(
            '/spaces/'.$this->spaceId.'/environments/'.$this->environmentId.'/content_types',
            $query ? $query->getQueryData() : []
        );
    }

    /**
     * @return Environment
     */
    public function getEnvironment()
    {
        if ($this->instanceRepository->has('Environment', $this->environmentId)) {
            return $this->instanceRepository->get('Environment', $this->environmentId);
        }

        // Because in the CDA there is no native endpoint for handling environments,
        // we create a fake one in order to assign the collection of locales to it.
        // We could be using any sort of fake resource for this, like a "LocaleCollection" type,
        // but given that previously locales were part of the space, whereas now they conceptually
        // belong to an environment, we choose this kind of abstraction.
        $locales = $this->request('GET', '/spaces/'.$this->spaceId.'/environments/'.$this->environmentId.'/locales');
        $environment = [
            'sys' => [
                'id' => $this->environmentId,
                'type' => 'Environment',
            ],
            'locales' => $locales['items'],
        ];

        return $this->builder->build($environment);
    }

    /**
     * @param string      $entryId
     * @param string|null $locale
     *
     * @return Entry
     */
    public function getEntry($entryId, $locale = null)
    {
        $locale = $locale ?: $this->defaultLocale;

        return $this->requestAndBuild(
            '/spaces/'.$this->spaceId.'/environments/'.$this->environmentId.'/entries/'.$entryId,
            ['locale' => $locale],
            'Entry',
            $entryId,
            $this->getLocaleForCacheKey($locale)
        );
    }

    /**
     * @param Query|null $query
     *
     * @return ResourceArray
     */
    public function getEntries(Query $query = null)
    {
        $queryData = $query ? $query->getQueryData() : [];
        if (!isset($queryData['locale'])) {
            $queryData['locale'] = $this->defaultLocale;
        }

        return $this->requestAndBuild(
            '/spaces/'.$this->spaceId.'/environments/'.$this->environmentId.'/entries',
            $queryData
        );
    }

    /**
     * @return Space
     */
    public function getSpace()
    {
        return $this->requestAndBuild(
            '/spaces/'.$this->spaceId,
            [],
            'Space',
            $this->spaceId
        );
    }

    /**
     * Resolve a link to its actual resource.
     *
     * @param Link        $link
     * @param string|null $locale
     *
     * @throws \InvalidArgumentException when encountering an unexpected link type
     *
     * @return ResourceInterface
     */
    public function resolveLink(Link $link, $locale = null)
    {
        switch ($link->getLinkType()) {
            case 'Asset':
                return $this->getAsset($link->getId(), $locale);
            case 'ContentType':
                return $this->getContentType($link->getId());
            case 'Entry':
                return $this->getEntry($link->getId(), $locale);
            case 'Environment':
                return $this->getEnvironment();
            case 'Space':
                return $this->getSpace();
            default:
                throw new \InvalidArgumentException(\sprintf(
                    'Trying to resolve link for unknown type "%s".',
                    $link->getLinkType()
                ));
        }
    }

    /**
     * Parse a JSON string.
     *
     * @param string $json
     *
     * @throws \InvalidArgumentException When attempting to parse JSON belonging to a different space or environment
     *
     * @return ResourceInterface|ResourceArray
     */
    public function parseJson($json)
    {
        return $this->builder->build(
            $this->scopedJsonDecoder->decode($json)
        );
    }

    /**
     * Internal method for \Contentful\Delivery\Synchronization\Manager.
     *
     * @param array $queryData
     *
     * @return mixed
     */
    public function syncRequest(array $queryData)
    {
        return $this->request('GET', '/spaces/'.$this->spaceId.'/environments/'.$this->environmentId.'/sync', [
            'query' => $queryData,
        ]);
    }

    /**
     * Returns true when using the Preview API.
     *
     * @return bool
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
     * @param string      $path
     * @param array       $query
     * @param string|null $type
     * @param string|null $resourceId
     * @param string|null $locale
     *
     * @return ResourceInterface|ResourceArray
     */
    private function requestAndBuild($path, array $query = [], $type = null, $resourceId = null, $locale = null)
    {
        if ($type && $resourceId && $this->instanceRepository->has($type, $resourceId, $locale)) {
            return $this->instanceRepository->get($type, $resourceId, $locale);
        }

        $response = $this->request('GET', $path, ['query' => $query]);

        return $this->builder->build($response);
    }
}

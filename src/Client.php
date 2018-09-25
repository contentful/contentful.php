<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery;

use Contentful\Core\Api\BaseClient;
use Contentful\Core\Api\Link;
use Contentful\Core\Resource\ResourceArray;
use Contentful\Core\Resource\ResourceInterface;
use Contentful\Core\ResourceBuilder\ResourceBuilderInterface;
use Contentful\Delivery\Resource\Asset;
use Contentful\Delivery\Resource\ContentType;
use Contentful\Delivery\Resource\Entry;
use Contentful\Delivery\Resource\Environment;
use Contentful\Delivery\Resource\Space;
use Contentful\Delivery\Synchronization\Manager;

/**
 * A Client is used to communicate the Contentful Delivery API.
 *
 * A Client is only responsible for one space and one environment.
 * When access to multiple spaces or environments is required, create multiple Clients.
 *
 * This class can be configured to use the Preview API instead of the Delivery API.
 * This grants access to not yet published content.
 */
class Client extends BaseClient implements ClientInterface
{
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
    private $isDeliveryApi;

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
     * @var LinkResolver
     */
    private $linkResolver;

    /**
     * Client constructor.
     *
     * @param string             $token         Delivery API Access Token for the space used with this Client
     * @param string             $spaceId       ID of the space used with this Client
     * @param string             $environmentId ID of the environment used with this Client
     * @param ClientOptions|null $options
     */
    public function __construct(
        string $token,
        string $spaceId,
        string $environmentId = 'master',
        ClientOptions $options = \null
    ) {
        $this->spaceId = $spaceId;
        $this->environmentId = $environmentId;

        $options = $options ?? new ClientOptions();

        // This works best as a negation:
        // We consider all as Delivery API except for those
        // explicitly set to Preview.
        $this->isDeliveryApi = self::URI_PREVIEW !== $options->getHost();
        $this->defaultLocale = $options->getDefaultLocale();

        $this->instanceRepository = new InstanceRepository(
            $this,
            $options->getCacheItemPool(),
            $options->hasCacheAutoWarmup(),
            $options->hasCacheContent()
        );
        $this->builder = new ResourceBuilder($this, $this->instanceRepository);
        $this->scopedJsonDecoder = new ScopedJsonDecoder($this->spaceId, $this->environmentId);
        $this->linkResolver = new LinkResolver($this);

        parent::__construct($token, $options->getHost(), $options->getLogger(), $options->getHttpClient());
    }

    /**
     * {@inheritdoc}
     */
    public function getApi(): string
    {
        return $this->isDeliveryApi ? self::API_DELIVERY : self::API_PREVIEW;
    }

    /**
     * @return string
     */
    public function getSpaceId(): string
    {
        return $this->spaceId;
    }

    /**
     * @return string
     */
    public function getEnvironmentId(): string
    {
        return $this->environmentId;
    }

    /**
     * @return ResourceBuilderInterface
     */
    public function getResourceBuilder(): ResourceBuilderInterface
    {
        return $this->builder;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSdkName(): string
    {
        return 'contentful.php';
    }

    /**
     * {@inheritdoc}
     */
    protected function getPackageName(): string
    {
        return 'contentful/contentful';
    }

    /**
     * {@inheritdoc}
     */
    protected function getApiContentType(): string
    {
        return 'application/vnd.contentful.delivery.v1+json';
    }

    /**
     * Returns the instance repository currently in use.
     *
     * @return InstanceRepository
     */
    public function getInstanceRepository(): InstanceRepositoryInterface
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
    private function getLocaleForCacheKey(string $locale = \null): string
    {
        if ($locale) {
            return $locale;
        }

        return $this->getEnvironment()->getDefaultLocale()->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getAsset(string $assetId, string $locale = \null): Asset
    {
        $locale = $locale ?: $this->defaultLocale;

        /** @var Asset $asset */
        $asset = $this->requestAndBuild(
            '/spaces/'.$this->spaceId.'/environments/'.$this->environmentId.'/assets/'.$assetId,
            ['locale' => $locale],
            'Asset',
            $assetId,
            $this->getLocaleForCacheKey($locale)
        );

        return $asset;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssets(Query $query = \null): ResourceArray
    {
        $queryData = $query ? $query->getQueryData() : [];
        if (!isset($queryData['locale'])) {
            $queryData['locale'] = $this->defaultLocale;
        }

        /** @var ResourceArray $assets */
        $assets = $this->requestAndBuild(
            '/spaces/'.$this->spaceId.'/environments/'.$this->environmentId.'/assets',
            $queryData
        );

        return $assets;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType(string $contentTypeId): ContentType
    {
        /** @var ContentType $contentType */
        $contentType = $this->requestAndBuild(
            '/spaces/'.$this->spaceId.'/environments/'.$this->environmentId.'/content_types/'.$contentTypeId,
            [],
            'ContentType',
            $contentTypeId
        );

        return $contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentTypes(Query $query = \null): ResourceArray
    {
        /** @var ResourceArray $contentTypes */
        $contentTypes = $this->requestAndBuild(
            '/spaces/'.$this->spaceId.'/environments/'.$this->environmentId.'/content_types',
            $query ? $query->getQueryData() : []
        );

        return $contentTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironment(): Environment
    {
        if ($this->instanceRepository->has('Environment', $this->environmentId)) {
            /** @var Environment $environment */
            $environment = $this->instanceRepository->get('Environment', $this->environmentId);

            return $environment;
        }

        // Because in the CDA there is no native endpoint for handling environments,
        // we create a fake one in order to assign the collection of locales to it.
        // We could be using any sort of fake resource for this, like a "LocaleCollection" type,
        // but given that previously locales were part of the space, whereas now they conceptually
        // belong to an environment, we choose this kind of abstraction.
        $locales = $this->request('GET', '/spaces/'.$this->spaceId.'/environments/'.$this->environmentId.'/locales');
        $environmentData = [
            'sys' => [
                'id' => $this->environmentId,
                'type' => 'Environment',
            ],
            'locales' => $locales['items'],
        ];

        /** @var Environment $environment */
        $environment = $this->builder->build($environmentData);

        return $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntry(string $entryId, string $locale = \null): Entry
    {
        $locale = $locale ?: $this->defaultLocale;

        /** @var Entry $entry */
        $entry = $this->requestAndBuild(
            '/spaces/'.$this->spaceId.'/environments/'.$this->environmentId.'/entries/'.$entryId,
            ['locale' => $locale],
            'Entry',
            $entryId,
            $this->getLocaleForCacheKey($locale)
        );

        return $entry;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntries(Query $query = \null): ResourceArray
    {
        $queryData = $query ? $query->getQueryData() : [];
        if (!isset($queryData['locale'])) {
            $queryData['locale'] = $this->defaultLocale;
        }

        /** @var ResourceArray $entries */
        $entries = $this->requestAndBuild(
            '/spaces/'.$this->spaceId.'/environments/'.$this->environmentId.'/entries',
            $queryData
        );

        return $entries;
    }

    /**
     * {@inheritdoc}
     */
    public function getSpace(): Space
    {
        /** @var Space $space */
        $space = $this->requestAndBuild(
            '/spaces/'.$this->spaceId,
            [],
            'Space',
            $this->spaceId
        );

        return $space;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveLink(Link $link, string $locale = \null): ResourceInterface
    {
        return $this->linkResolver->resolveLink($link, [
            'locale' => (string) $locale,
        ]);
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
    public function parseJson(string $json)
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
     * Returns true when using the Delivery API.
     *
     * @return bool
     */
    public function isDeliveryApi(): bool
    {
        return $this->isDeliveryApi;
    }

    /**
     * Returns true when using the Preview API.
     *
     * @return bool
     */
    public function isPreviewApi(): bool
    {
        return !$this->isDeliveryApi;
    }

    /**
     * Get an instance of the synchronization manager. Note that with the Preview API only an initial sync
     * is giving valid results.
     *
     * @return Manager
     *
     * @see https://www.contentful.com/developers/docs/concepts/sync/ Sync API
     */
    public function getSynchronizationManager(): Manager
    {
        return new Manager($this, $this->builder, $this->isDeliveryApi);
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
    private function requestAndBuild(
        string $path,
        array $query = [],
        string $type = \null,
        string $resourceId = \null,
        string $locale = \null
    ) {
        if ($type && $resourceId && $this->instanceRepository->has($type, $resourceId, $locale)) {
            return $this->instanceRepository->get($type, $resourceId, $locale);
        }

        $response = (array) $this->request('GET', $path, ['query' => $query]);

        return $this->builder->build($response);
    }
}

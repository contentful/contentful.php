<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2019 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery;

use Contentful\Core\Api\BaseClient;
use Contentful\Core\Api\Exception;
use Contentful\Core\Api\Link;
use Contentful\Core\Api\LinkResolverInterface;
use Contentful\Core\Resource\ResourceArray;
use Contentful\Core\Resource\ResourceInterface;
use Contentful\Core\Resource\ResourcePoolInterface;
use Contentful\Core\ResourceBuilder\ResourceBuilderInterface;
use Contentful\Delivery\Client\ClientInterface;
use Contentful\Delivery\Client\JsonDecoderClientInterface;
use Contentful\Delivery\Client\SynchronizationClientInterface;
use Contentful\Delivery\Resource\Asset;
use Contentful\Delivery\Resource\ContentType;
use Contentful\Delivery\Resource\Entry;
use Contentful\Delivery\Resource\Environment;
use Contentful\Delivery\Resource\Space;
use Contentful\Delivery\ResourcePool\Factory;
use Contentful\Delivery\Synchronization\Manager;
use Contentful\RichText\Parser;

/**
 * A Client is used to communicate the Contentful Delivery API.
 *
 * A Client is only responsible for one space and one environment.
 * When access to multiple spaces or environments is required, create multiple Clients.
 *
 * This class can be configured to use the Preview API instead of the Delivery API.
 * This grants access to not yet published content.
 */
class Client extends BaseClient implements ClientInterface, SynchronizationClientInterface, JsonDecoderClientInterface
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
     * @var ResourceBuilderInterface
     */
    private $builder;

    /**
     * @var ResourcePoolInterface
     */
    private $resourcePool;

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
     * @var LinkResolverInterface
     */
    private $linkResolver;

    /**
     * @var Parser
     */
    private $richTextParser;

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

        $this->resourcePool = Factory::create($this, $options);
        $this->scopedJsonDecoder = new ScopedJsonDecoder($this->spaceId, $this->environmentId);
        $this->linkResolver = new LinkResolver($this, $this->resourcePool);
        $this->richTextParser = new Parser($this->linkResolver);
        $this->builder = new ResourceBuilder($this, $this->resourcePool, $this->richTextParser);

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
     * @return Parser
     */
    public function getRichTextParser(): Parser
    {
        return $this->richTextParser;
    }

    /**
     * {@inheritdoc}
     */
    protected static function getSdkName(): string
    {
        return 'contentful.php';
    }

    /**
     * {@inheritdoc}
     */
    protected static function getPackageName(): string
    {
        return 'contentful/contentful';
    }

    /**
     * {@inheritdoc}
     */
    protected static function getApiContentType(): string
    {
        return 'application/vnd.contentful.delivery.v1+json';
    }

    /**
     * Returns the resource pool currently in use.
     *
     * @return ResourcePoolInterface
     */
    public function getResourcePool(): ResourcePoolInterface
    {
        return $this->resourcePool;
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
        $asset = $this->requestWithCache(
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
        $assets = $this->request(
            'GET',
            '/spaces/'.$this->spaceId.'/environments/'.$this->environmentId.'/assets',
            ['query' => $queryData]
        );

        return $assets;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType(string $contentTypeId): ContentType
    {
        /** @var ContentType $contentType */
        $contentType = $this->requestWithCache(
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
        $contentTypes = $this->request(
            'GET',
            '/spaces/'.$this->spaceId.'/environments/'.$this->environmentId.'/content_types',
            ['query' => $query ? $query->getQueryData() : []]
        );

        return $contentTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironment(): Environment
    {
        if ($this->resourcePool->has('Environment', $this->environmentId)) {
            /** @var Environment $environment */
            $environment = $this->resourcePool->get('Environment', $this->environmentId);

            return $environment;
        }

        // Because in the CDA there is no native endpoint for handling environments,
        // we create a fake one in order to assign the collection of locales to it.
        // We could be using any sort of fake resource for this, like a "LocaleCollection" type,
        // but given that previously locales were part of the space, whereas now they conceptually
        // belong to an environment, we choose this kind of abstraction.
        $locales = $this->callApi(
            'GET',
            '/spaces/'.$this->spaceId.'/environments/'.$this->environmentId.'/locales'
        );
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
        $entry = $this->requestWithCache(
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
        $entries = $this->request(
            'GET',
            '/spaces/'.$this->spaceId.'/environments/'.$this->environmentId.'/entries',
            ['query' => $queryData]
        );

        return $entries;
    }

    /**
     * {@inheritdoc}
     */
    public function getSpace(): Space
    {
        try {
            /** @var Space $space */
            $space = $this->requestWithCache(
                '/spaces/'.$this->spaceId,
                [],
                'Space',
                $this->spaceId
            );
        } catch (Exception $exception) {
            // An edge case with environments might result in space data not being available.
            // As it *is* technically needed, we provide a fake space object.
            /** @var Space $space */
            $space = $this->builder->build([
                'sys' => [
                    'id' => $this->spaceId,
                    'type' => 'Space',
                ],
                'name' => $this->spaceId,
            ]);
        }

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
     * {@inheritdoc}
     */
    public function resolveLinkCollection(array $links, string $locale = \null): array
    {
        return $this->linkResolver->resolveLinkCollection($links, [
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
     * {@inheritdoc}
     */
    public function getSynchronizationManager(): Manager
    {
        return new Manager($this, $this->builder, $this->isDeliveryApi);
    }

    /**
     * {@inheritdoc}
     */
    public function syncRequest(array $queryData): array
    {
        return $this->callApi('GET', '/spaces/'.$this->spaceId.'/environments/'.$this->environmentId.'/sync', [
            'query' => $queryData,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function request(string $method, string $uri, array $options = []): ResourceInterface
    {
        $response = $this->callApi('GET', $uri, $options);

        return $this->builder->build($response);
    }

    /**
     * @param string      $uri
     * @param array       $query
     * @param string|null $type
     * @param string|null $resourceId
     * @param string|null $locale
     *
     * @return ResourceInterface|ResourceArray
     */
    private function requestWithCache(
        string $uri,
        array $query = [],
        string $type = \null,
        string $resourceId = \null,
        string $locale = \null
    ) {
        if ($type && $resourceId && $this->resourcePool->has($type, $resourceId, ['locale' => $locale])) {
            return $this->resourcePool->get($type, $resourceId, ['locale' => $locale]);
        }

        return $this->request('GET', $uri, ['query' => $query]);
    }
}

<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

use Contentful\Client as BaseClient;
use Contentful\Delivery\Synchronization\Manager;
use Contentful\Query as BaseQuery;

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
     * Client constructor.
     *
     * @param string                $token         Delivery API Access Token for the space used with this Client
     * @param string                $spaceId       ID of the space used with this Client.
     * @param bool                  $preview       True to use the Preview API.
     * @param string|null           $defaultLocale The default is to fetch the Space's default locale. Set to a locale
     *                                             string, e.g. "en-US" to fetch content in that locale. Set it to "*"
     *                                             to fetch content in all locales.
     * @param array                 $options       An array of optional configuration options. The following keys are possible:
     *                                              * guzzle      Override the guzzle instance used by the Contentful client
     *                                              * logger      Inject a Contentful logger
     *                                              * uriOverride Override the uri that is used to connect to the Contentful API (e.g. 'https://cdn.contentful.com/'). The trailing slash is required.
     *
     * @api
     */
    public function __construct($token, $spaceId, $preview = false, $defaultLocale = null, array $options = [])
    {
        $baseUri = $preview ? 'https://preview.contentful.com/' : 'https://cdn.contentful.com/';
        $api = $preview ? 'PREVIEW' : 'DELIVERY';

        $options = array_replace([
            'guzzle' => null,
            'logger' => null,
            'uriOverride' => null,
        ], $options);

        $guzzle = $options['guzzle'];
        $logger = $options['logger'];
        $uriOverride = $options['uriOverride'];

        if ($uriOverride !== null) {
            $baseUri = $uriOverride;
        }
        $baseUri .= 'spaces/';

        $instanceCache = new InstanceCache;

        parent::__construct($token, $baseUri . $spaceId . '/', $api, $logger, $guzzle);

        $this->preview = $preview;
        $this->instanceCache = $instanceCache;
        $this->builder = new ResourceBuilder($this, $instanceCache, $spaceId);
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
     * @param  string      $id
     * @param  string|null $locale
     *
     * @return Asset
     *
     * @api
     */
    public function getAsset($id, $locale = null)
    {
        $locale = $locale === null ? $this->defaultLocale : $locale;
        return $this->requestAndBuild('GET', 'assets/' . $id, [
            'query' => ['locale' => $locale]
        ]);
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

        return $this->requestAndBuild('GET', 'assets', [
            'query' => $queryData
        ]);
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
        if ($this->instanceCache->hasContentType($id)) {
            return $this->instanceCache->getContentType($id);
        }

        return $this->requestAndBuild('GET', 'content_types/' . $id);
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
        return $this->requestAndBuild('GET', 'content_types', [
            'query' => $query->getQueryData()
        ]);
    }

    /**
     * @param  string      $id
     * @param  string|null $locale
     *
     * @return EntryInterface
     *
     * @api
     */
    public function getEntry($id, $locale = null)
    {
        $locale = $locale === null ? $this->defaultLocale : $locale;
        return $this->requestAndBuild('GET', 'entries/' . $id, [
            'query' => ['locale' => $locale]
        ]);
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

        return $this->requestAndBuild('GET', 'entries', [
            'query' => $queryData
        ]);
    }

    /**
     * @return \Contentful\Delivery\Space
     *
     * @api
     */
    public function getSpace()
    {
        if ($this->instanceCache->hasSpace()) {
            return $this->instanceCache->getSpace();
        }

        return $this->requestAndBuild('GET', '');
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

        return $this->builder->buildObjectsFromRawData($data);
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
}

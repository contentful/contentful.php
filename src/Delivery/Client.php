<?php
/**
 * @copyright 2015 Contentful GmbH
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
     * Client constructor.
     *
     * @param string  $token   Delivery API Access Token for the space used with this Client
     * @param string  $spaceId ID of the space used with this Client.
     * @param bool    $preview True to use the Preview API.
     *
     * @api
     */
    public function __construct($token, $spaceId, $preview = false)
    {
        $baseUri = $preview ? 'https://preview.contentful.com/spaces/' : 'https://cdn.contentful.com/spaces/';

        $instanceCache = new InstanceCache;

        parent::__construct($token, $baseUri . $spaceId . '/', $instanceCache);

        $this->preview = $preview;
        $this->instanceCache = $instanceCache;
        $this->builder = new ResourceBuilder($this, $instanceCache, $spaceId);
    }

    /**
     * @param  string $id
     *
     * @return Asset
     *
     * @api
     */
    public function getAsset($id)
    {
        if ($this->instanceCache->hasAsset($id)) {
            return $this->instanceCache->getAsset($id);
        }

        return $this->requestAndBuild('GET', 'assets/' . $id, [
            'query' => ['locale' => '*']
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
        $queryData['locale'] = '*';

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
     * @param  string $id
     *
     * @return EntryInterface
     *
     * @api
     */
    public function getEntry($id)
    {
        if ($this->instanceCache->hasEntry($id)) {
            return $this->instanceCache->getEntry($id);
        }

        return $this->requestAndBuild('GET', 'entries/' . $id, [
            'query' => ['locale' => '*']
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
        $queryData['locale'] = '*';

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
     * @throws SpaceMismatchException When attemptiting to revive JSON belonging to a different space
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

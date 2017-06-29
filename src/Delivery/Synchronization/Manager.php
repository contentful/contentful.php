<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Synchronization;

use Contentful\Delivery\Client;
use Contentful\Delivery\ResourceBuilder;

/**
 * The synchronization Manager can be used to sync a Space to this server.
 *
 * It provides notifications about added, updated and deleted assets and entries.
 *
 * @see https://www.contentful.com/developers/docs/concepts/sync/ Sync API
 * @api
 */
class Manager
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var ResourceBuilder
     */
    private $builder;

    /**
     * @var bool
     */
    private $preview;

    /**
     * Manager constructor.
     *
     * Do not instantiate this class yourself, use Contentful\Delivery\Client::getSynchronizationManager instead.
     *
     * @param Client          $client
     * @param ResourceBuilder $builder
     * @param bool            $preview
     *
     * @see \Contentful\Delivery\Client::getSynchronizationManager()
     * @internal
     */
    public function __construct(Client $client, ResourceBuilder $builder, $preview)
    {
        $this->client = $client;
        $this->builder = $builder;
        $this->preview = $preview;
    }

    /**
     * Starts a new Synchronization. Will contain all the Resources currently present in the space.
     *
     * By calling Result::isDone it can be checked if there's another page of results, if so call `continueSync` to get the next page.
     *
     * A Query can be used to return only a subset of the space.
     *
     * @param  Query|null $query
     *
     * @return Result
     *
     * @api
     */
    public function startSync(Query $query = null)
    {
        $query = $query !== null ? $query : new Query;
        $response = $this->client->syncRequest($query->getQueryData());

        return $this->buildResult($response);
    }

    /**
     * Continues the synchronization either at the next page or with the results since the initial synchronization.
     *
     * @param  string $token
     *
     * @return Result
     *
     * @throws \RuntimeException If this method is used for a subsequent sync when used with the Preview API.
     *
     * @api
     */
    public function continueSync($token)
    {
        if ($token instanceof Result) {
            if ($this->preview && $token->isDone()) {
                throw new \RuntimeException('Can not continue syncing when using the Content Preview API.');
            }
            $token = $token->getToken();
        }

        $response = $this->client->syncRequest(['sync_token' => $token]);

        return $this->buildResult($response);
    }

    /**
     * Build a Result from the API response.
     *
     * @param  array $data
     *
     * @return Result
     */
    private function buildResult(array $data)
    {
        if (isset($data['nextSyncUrl'])) {
            $done = true;
            $token = $this->getTokenFromUrl($data['nextSyncUrl']);
        } else {
            $done = false;
            $token = $this->getTokenFromUrl($data['nextPageUrl']);
        }
        $items = array_map(function ($item) {
            if (isset($item['sys']['locale'])) {
                unset($item['sys']['locale']);
            }

            return $this->builder->buildObjectsFromRawData($item);
        }, $data['items']);

        return new Result($items, $token, $done);
    }

    /**
     * Parses the sync_token out of an URL supplied by the API.
     *
     * @param  string $url The nextSyncUrl or nextPageUrl from an API response.
     *
     * @return string
     */
    private function getTokenFromUrl($url)
    {
        $queryValues = [];
        parse_str(parse_url($url, PHP_URL_QUERY), $queryValues);

        return $queryValues['sync_token'];
    }
}

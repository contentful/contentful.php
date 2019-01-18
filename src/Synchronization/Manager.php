<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2019 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Synchronization;

use Contentful\Core\Resource\ResourceInterface;
use Contentful\Core\ResourceBuilder\ResourceBuilderInterface;
use Contentful\Delivery\Client\SynchronizationClientInterface;

/**
 * The synchronization Manager can be used to sync a Space to this server.
 *
 * It provides notifications about added, updated and deleted assets and entries.
 *
 * @see https://www.contentful.com/developers/docs/concepts/sync/ Sync API
 */
class Manager
{
    /**
     * @var SynchronizationClientInterface
     */
    private $client;

    /**
     * @var ResourceBuilderInterface
     */
    private $builder;

    /**
     * @var bool
     */
    private $isDeliveryApi;

    /**
     * Manager constructor.
     *
     * Do not instantiate this class yourself,
     * use SynchronizationClientInterface::getSynchronizationManager() instead.
     *
     * @param SynchronizationClientInterface $client
     * @param ResourceBuilderInterface       $builder
     * @param bool                           $isDeliveryApi
     *
     * @see Client::getSynchronizationManager()
     */
    public function __construct(
        SynchronizationClientInterface $client,
        ResourceBuilderInterface $builder,
        bool $isDeliveryApi
    ) {
        $this->client = $client;
        $this->builder = $builder;
        $this->isDeliveryApi = $isDeliveryApi;
    }

    /**
     * @param string|null $token
     * @param Query|null  $query
     *
     * @return \Generator An instance of Result wrapped in a Generator object
     */
    public function sync(string $token = \null, Query $query = \null): \Generator
    {
        do {
            $result = $token ? $this->continueSync($token) : $this->startSync($query);

            yield $result;

            $token = $result->getToken();
        } while (!$result->isDone());
    }

    /**
     * Starts a new Synchronization.
     * The result will contain all the resources currently present in the space.
     * By calling Result::isDone(), it can be checked if there's another page of results,
     * if so call `continueSync` to get the next page.
     *
     * A Query object can be used to return only a subset of the space.
     *
     * @param Query|null $query
     *
     * @return Result
     */
    public function startSync(Query $query = \null)
    {
        $query = \null !== $query ? $query : new Query();
        $response = $this->client->syncRequest($query->getQueryData());

        return $this->buildResult($response);
    }

    /**
     * Continues the synchronization either at the next page,
     * or with the results since the initial synchronization.
     *
     * @param string|Result $token
     *
     * @throws \RuntimeException if this method is used for a subsequent sync when used with the Preview API
     *
     * @return Result
     */
    public function continueSync($token): Result
    {
        if ($token instanceof Result) {
            if (!$this->isDeliveryApi && $token->isDone()) {
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
     * @param array $data
     *
     * @return Result
     */
    private function buildResult(array $data): Result
    {
        $token = $this->getTokenFromResponse($data);
        $done = isset($data['nextSyncUrl']);

        $items = \array_map(function (array $item): ResourceInterface {
            return $this->builder->build($item);
        }, $data['items']);

        return new Result($items, $token, $done);
    }

    /**
     * Parses the sync_token out of an URL supplied by the API.
     *
     * @param array $data The API response
     *
     * @return string
     */
    private function getTokenFromResponse(array $data): string
    {
        $url = $data['nextSyncUrl'] ?? $data['nextPageUrl'];

        $queryValues = [];
        \parse_str(\parse_url($url, \PHP_URL_QUERY), $queryValues);

        return $queryValues['sync_token'];
    }
}

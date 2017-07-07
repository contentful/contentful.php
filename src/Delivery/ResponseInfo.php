<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

use Contentful\ResponseInfo as BaseResponseInfo;
use GuzzleHttp\Psr7\Response;

/**
 * {@inheritdoc}
 */
class ResponseInfo extends BaseResponseInfo
{
    /**
     * It can be either "HIT" or "MISS".
     *
     * @var string
     */
    private $cache;

    /**
     * @var int
     */
    private $cacheHits;

    /**
     * {@inheritdoc}
     */
    public function __construct(Response $response)
    {
        parent::__construct($response);

        $this->cache = $response->getHeaderLine('X-Cache');
        $this->cacheHits = (int) $response->getHeaderLine('X-Cache-Hits');
    }

    /**
     * @return string
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @return int
     */
    public function getCacheHits()
    {
        return $this->cacheHits;
    }
}

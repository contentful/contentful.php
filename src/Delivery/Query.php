<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

use Contentful\Query as BaseQuery;

/**
 * A Query is used to filter and order collections when making API requests.
 */
class Query extends BaseQuery
{
    /**
     * The amount of levels of links that should be resolved.
     *
     * @var int|null
     */
    private $include;

    /**
     * The locale for which to query.
     *
     * @var string|null
     */
    private $locale;

    /**
     * The ID of the entry to look for.
     *
     * @var string|null
     */
    private $linksToEntry;

    /**
     * The ID of the asset to look for.
     *
     * @var string|null
     */
    private $linksToAsset;

    /**
     * Returns the parameters to execute this query.
     *
     * @return array
     */
    public function getQueryData()
    {
        $query = parent::getQueryData();
        if (null !== $this->include) {
            $query['include'] = $this->include;
        }
        if (null !== $this->locale) {
            $query['locale'] = $this->locale;
        }
        if (null !== $this->linksToEntry) {
            $query['links_to_entry'] = $this->linksToEntry;
        }
        if (null !== $this->linksToAsset) {
            $query['links_to_asset'] = $this->linksToAsset;
        }

        return $query;
    }

    /**
     * Set the amount of levels of links that should be resolved.
     *
     * @param int|null $include
     *
     * @return $this
     */
    public function setInclude($include)
    {
        $this->include = $include;

        return $this;
    }

    /**
     * Sets the locale for which content should be retrieved. Set it to `*` to retrieve all locales.
     *
     * @param string|null $locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Filters for all entries that link to an entry.
     *
     * @param string $entryId
     *
     * @return $this
     */
    public function linksToEntry($entryId)
    {
        $this->linksToEntry = $entryId;

        return $this;
    }

    /**
     * Filters for all entries that link to an asset.
     *
     * @param string $assetId
     *
     * @return $this
     */
    public function linksToAsset($assetId)
    {
        $this->linksToAsset = $assetId;

        return $this;
    }
}

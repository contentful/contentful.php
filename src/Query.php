<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

use Contentful\Core\Api\BaseQuery;

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
}

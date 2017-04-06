<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

use \Contentful\Query as BaseQuery;

/**
 * A Query is used to filter and order collections when making API requests.
 *
 * @api
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
     * The locale for which to query
     *
     * @var string|null
     */
    private $locale;

    /**
     * Returns the parameters to execute this query.
     *
     * @return array
     *
     * @api
     */
    public function getQueryData()
    {
        $query = parent::getQueryData();
        if ($this->include !== null) {
            $query['include'] = $this->include;
        }
        if ($this->locale !== null) {
            $query['locale'] = $this->locale;
        }

        return $query;
    }

    /**
     * Set the amount of levels of links that should be resolved.
     *
     * @param  int|null $include
     *
     * @return $this
     *
     * @api
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
     *
     * @api
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }
}

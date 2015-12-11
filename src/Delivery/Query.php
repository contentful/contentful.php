<?php
/**
 * @copyright 2015 Contentful GmbH
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

        return $query;
    }

    /**
     * Get the amount of levels of links that should be resolved.
     *
     * @return int|null
     *
     * @api
     */
    public function getInclude()
    {
        return $this->include;
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
}

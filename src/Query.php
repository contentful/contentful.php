<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

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
     */
    public function getQueryData(): array
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
     * @return $this
     */
    public function setInclude(?int $include = null)
    {
        $this->include = $include;

        return $this;
    }

    /**
     * Sets the locale for which content should be retrieved. Set it to `*` to retrieve all locales.
     *
     * @return $this
     */
    public function setLocale(?string $locale = null)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get the locale currently set for this query.
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }
}

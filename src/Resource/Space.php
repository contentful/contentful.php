<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Resource;

/**
 * The Space class represents a single space identified by its ID and holding some metadata.
 */
class Space extends BaseResource
{
    /**
     * @var string
     */
    protected $name;

    /**
     * Returns the name of this space.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'sys' => $this->sys,
            'name' => $this->name,
        ];
    }
}

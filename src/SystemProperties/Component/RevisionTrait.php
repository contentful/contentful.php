<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\SystemProperties\Component;

trait RevisionTrait
{
    /**
     * @var int
     */
    protected $revision;

    /**
     * @return int
     */
    public function getRevision(): int
    {
        return $this->revision;
    }
}

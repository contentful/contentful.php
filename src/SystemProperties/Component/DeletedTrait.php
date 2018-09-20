<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\SystemProperties\Component;

use Contentful\Core\Api\DateTimeImmutable;

trait DeletedTrait
{
    /**
     * @var DateTimeImmutable
     */
    protected $deletedAt;

    /**
     * @return DateTimeImmutable
     */
    public function getDeletedAt(): DateTimeImmutable
    {
        return $this->deletedAt;
    }
}

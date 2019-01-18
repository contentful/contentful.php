<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2019 Contentful GmbH
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
     * @param array $data
     */
    protected function initDeletedAt(array $data)
    {
        $this->deletedAt = new DateTimeImmutable($data['deletedAt']);
    }

    /**
     * @return array
     */
    protected function jsonSerializeDeletedAt(): array
    {
        return [
            'deletedAt' => $this->deletedAt,
        ];
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDeletedAt(): DateTimeImmutable
    {
        return $this->deletedAt;
    }
}

<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
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

    protected function initDeletedAt(array $data)
    {
        $this->deletedAt = new DateTimeImmutable($data['deletedAt']);
    }

    protected function jsonSerializeDeletedAt(): array
    {
        return [
            'deletedAt' => $this->deletedAt,
        ];
    }

    public function getDeletedAt(): DateTimeImmutable
    {
        return $this->deletedAt;
    }
}

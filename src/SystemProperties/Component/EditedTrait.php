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

trait EditedTrait
{
    use RevisionTrait;

    /**
     * @var DateTimeImmutable
     */
    protected $createdAt;

    /**
     * @var DateTimeImmutable
     */
    protected $updatedAt;

    /**
     * @param array $data
     */
    protected function initEdited(array $data)
    {
        $this->initRevision($data);
        $this->createdAt = new DateTimeImmutable($data['createdAt']);
        $this->updatedAt = new DateTimeImmutable($data['updatedAt']);
    }

    /**
     * @return array
     */
    protected function jsonSerializeEdited(): array
    {
        return \array_merge($this->jsonSerializeRevision(), [
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ]);
    }

    /**
     * @return DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}

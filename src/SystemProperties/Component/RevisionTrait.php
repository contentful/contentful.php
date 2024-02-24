<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
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

    protected function initRevision(array $data)
    {
        $this->revision = $data['revision'] ?? $data['version'] ?? 1;
    }

    protected function jsonSerializeRevision(string $name = 'revision'): array
    {
        return [
            $name => $this->revision,
        ];
    }

    public function getRevision(): int
    {
        return $this->revision;
    }
}

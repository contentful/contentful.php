<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\SystemProperties\Component;

use Contentful\Delivery\Resource\Tag;

trait TagTrait
{
    /**
     * @var Tag[]
     */
    protected $tags;

    public function initTags(array $tags)
    {
        $this->tags = $tags;
    }

    protected function jsonSerializeSpace(): array
    {
        return [
            'tags' => array_map(function ($tag) { return $tag->asLink(); }, $this->tags),
        ];
    }

    public function getTags(): array
    {
        return $this->tags;
    }
}

<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\SystemProperties\Component;

use Contentful\Delivery\Resource\ContentType;

trait ContentTypeTrait
{
    /**
     * @var ContentType
     */
    protected $contentType;

    protected function initContentType(array $data)
    {
        $this->contentType = $data['contentType'];
    }

    protected function jsonSerializeContentType(): array
    {
        return [
            'contentType' => $this->contentType->asLink(),
        ];
    }

    public function getContentType(): ContentType
    {
        return $this->contentType;
    }
}

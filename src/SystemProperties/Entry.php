<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\SystemProperties;

class Entry extends LocalizedResource
{
    use Component\ContentTypeTrait;

    /**
     * Entry constructor.
     */
    public function __construct(array $sys)
    {
        parent::__construct($sys);

        $this->initContentType($sys);
    }

    public function jsonSerialize(): array
    {
        return array_merge(
            parent::jsonSerialize(),
            $this->jsonSerializeContentType()
        );
    }
}

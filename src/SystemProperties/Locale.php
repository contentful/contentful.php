<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\SystemProperties;

class Locale extends BaseSystemProperties
{
    use Component\RevisionTrait;

    /**
     * Locale constructor.
     */
    public function __construct(array $sys)
    {
        parent::__construct($sys);

        $this->initRevision($sys);
    }

    public function jsonSerialize(): array
    {
        return array_merge(
            parent::jsonSerialize(),
            $this->jsonSerializeRevision('version')
        );
    }
}

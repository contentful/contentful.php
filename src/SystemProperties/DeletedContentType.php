<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\SystemProperties;

use Contentful\Core\Api\DateTimeImmutable;

class DeletedContentType extends ContentType
{
    use Component\DeletedTrait;

    /**
     * DeletedContentType constructor.
     *
     * @param array $sys
     */
    public function __construct(array $sys)
    {
        parent::__construct($sys);

        $this->deletedAt = new DateTimeImmutable($sys['deletedAt']);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return \array_filter(\array_merge(parent::jsonSerialize(), [
            'deletedAt' => $this->deletedAt,
        ]));
    }
}

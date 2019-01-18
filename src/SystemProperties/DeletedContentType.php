<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2019 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\SystemProperties;

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

        $this->initDeletedAt($sys);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return \array_merge(
            parent::jsonSerialize(),
            $this->jsonSerializeDeletedAt()
        );
    }
}

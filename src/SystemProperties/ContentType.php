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

class ContentType extends BaseSystemProperties
{
    use Component\EditedTrait,
        Component\EnvironmentTrait,
        Component\SpaceTrait;

    /**
     * ContentType constructor.
     *
     * @param array $sys
     */
    public function __construct(array $sys)
    {
        parent::__construct($sys);

        $this->revision = $sys['revision'];
        $this->environment = $sys['environment'];
        $this->space = $sys['space'];
        $this->createdAt = new DateTimeImmutable($sys['createdAt']);
        $this->updatedAt = new DateTimeImmutable($sys['updatedAt']);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return \array_filter(\array_merge(parent::jsonSerialize(), [
            'revision' => $this->revision,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'environment' => $this->environment->asLink(),
            'space' => $this->space->asLink(),
        ]));
    }
}

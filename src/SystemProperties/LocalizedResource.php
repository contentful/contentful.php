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

abstract class LocalizedResource extends BaseSystemProperties
{
    use Component\EditedTrait,
        Component\EnvironmentTrait,
        Component\LocaleTrait,
        Component\SpaceTrait;

    /**
     * LocalizedResource constructor.
     *
     * @param array $sys
     */
    public function __construct(array $sys)
    {
        parent::__construct($sys);

        $this->revision = $sys['revision'];
        $this->locale = $sys['locale'] ?? \null;
        $this->space = $sys['space'];
        $this->environment = $sys['environment'];
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
            'locale' => $this->locale,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'space' => $this->space->asLink(),
            'environment' => $this->environment->asLink(),
        ]));
    }
}

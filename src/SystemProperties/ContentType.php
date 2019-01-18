<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2019 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\SystemProperties;

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

        $this->initEdited($sys);
        $this->initEnvironment($sys);
        $this->initSpace($sys);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return \array_filter(\array_merge(
            parent::jsonSerialize(),
            $this->jsonSerializeEdited(),
            $this->jsonSerializeEnvironment(),
            $this->jsonSerializeSpace()
        ));
    }
}

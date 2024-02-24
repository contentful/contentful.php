<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\SystemProperties;

class ContentType extends BaseSystemProperties
{
    use Component\EditedTrait;
    use Component\EnvironmentTrait;
    use Component\SpaceTrait;

    /**
     * ContentType constructor.
     */
    public function __construct(array $sys)
    {
        parent::__construct($sys);

        $this->initEdited($sys);
        $this->initEnvironment($sys);
        $this->initSpace($sys);
    }

    public function jsonSerialize(): array
    {
        return array_filter(array_merge(
            parent::jsonSerialize(),
            $this->jsonSerializeEdited(),
            $this->jsonSerializeEnvironment(),
            $this->jsonSerializeSpace()
        ));
    }
}

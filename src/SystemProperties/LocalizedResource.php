<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\SystemProperties;

abstract class LocalizedResource extends BaseSystemProperties
{
    use Component\EditedTrait;
    use Component\EnvironmentTrait;
    use Component\LocaleTrait;
    use Component\SpaceTrait;

    /**
     * LocalizedResource constructor.
     */
    public function __construct(array $sys)
    {
        parent::__construct($sys);

        $this->initEdited($sys);
        $this->initEnvironment($sys);
        $this->initLocale($sys);
        $this->initSpace($sys);
    }

    public function jsonSerialize(): array
    {
        return array_filter(array_merge(
            parent::jsonSerialize(),
            $this->jsonSerializeEdited(),
            $this->jsonSerializeEnvironment(),
            $this->jsonSerializeLocale(),
            $this->jsonSerializeSpace()
        ));
    }
}

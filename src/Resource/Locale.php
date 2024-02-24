<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Resource;

use Contentful\Delivery\SystemProperties\Locale as SystemProperties;

/**
 * Value object encoding a locale.
 */
class Locale extends BaseResource
{
    /**
     * @var SystemProperties
     */
    protected $sys;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $fallbackCode;

    /**
     * @var bool
     */
    protected $default = false;

    public function getSystemProperties(): SystemProperties
    {
        return $this->sys;
    }

    /**
     * Returns the locale code.
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Returns the human readable name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns true if this is the default locale for the space.
     */
    public function isDefault(): bool
    {
        return $this->default;
    }

    /**
     * Returns the code of the locale used for for the fallback.
     *
     * @return string|null
     */
    public function getFallbackCode()
    {
        return $this->fallbackCode;
    }

    public function jsonSerialize(): array
    {
        $locale = [
            'sys' => $this->sys,
            'code' => $this->code,
            'default' => $this->default,
            'name' => $this->name,
            'fallbackCode' => $this->fallbackCode,
        ];

        return $locale;
    }
}

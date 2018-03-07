<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Resource;

/**
 * Value object encoding a locale.
 */
class Locale extends BaseResource
{
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

    /**
     * Returns the locale code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Returns the human readable name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns true if this is the default locale for the space.
     *
     * @return bool
     */
    public function isDefault()
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

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $locale = [
            'code' => $this->code,
            'default' => $this->default,
            'name' => $this->name,
            'fallbackCode' => $this->fallbackCode,
        ];

        if ($this->sys) {
            $locale['sys'] = $this->sys;
        }

        return $locale;
    }
}

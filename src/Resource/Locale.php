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
    private $code;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $fallbackCode;

    /**
     * @var bool
     */
    private $default;

    /**
     * Locale constructor.
     *
     * @param string $code         Locale code
     * @param string $name         Human readable name
     * @param string $fallbackCode The code of the locale used for for the fallback
     * @param bool   $default      Whether this is the default locale
     */
    public function __construct($code, $name, $fallbackCode, $default = false)
    {
        $this->code = $code;
        $this->name = $name;
        $this->fallbackCode = $fallbackCode;
        $this->default = $default;
    }

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
     * @return string
     */
    public function getFallbackCode()
    {
        return $this->fallbackCode;
    }

    /**
     * Returns an object to be used by `json_encode` to serialize objects of this class.
     *
     * @return object
     *
     * @see http://php.net/manual/en/jsonserializable.jsonserialize.php JsonSerializable::jsonSerialize
     */
    public function jsonSerialize()
    {
        return (object) [
            'code' => $this->code,
            'default' => $this->default,
            'name' => $this->name,
            'fallbackCode' => $this->fallbackCode,
        ];
    }
}

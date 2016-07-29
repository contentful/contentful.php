<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

/**
 * Value object encoding a locale.
 *
 * @api
 */
class Locale implements \JsonSerializable
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
     * @var bool
     */
    private $default;

    /**
     * Locale constructor.
     *
     * @param string $code    Locale code
     * @param string $name    Human readable name
     * @param bool   $default Whether this is the default locale
     *
     * @api
     */
    public function __construct($code, $name, $default = false)
    {
        $this->code = $code;
        $this->name = $name;
        $this->default = $default;
    }

    /**
     * Returns the locale code
     *
     * @return string
     *
     * @api
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Returns the human readable name
     *
     * @return string
     *
     * @api
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns true if this is the default locale for the space.
     *
     * @return bool
     *
     * @api
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * Returns an object to be used by `json_encode` to serialize objects of this class.
     *
     * @return object
     *
     * @see http://php.net/manual/en/jsonserializable.jsonserialize.php JsonSerializable::jsonSerialize
     *
     * @api
     */
    public function jsonSerialize()
    {
        return (object) [
            'code' => $this->code,
            'default' => $this->default,
            'name' => $this->name
        ];
    }
}

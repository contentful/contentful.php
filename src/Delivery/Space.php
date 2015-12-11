<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

/**
 * The Space class represents a single space identified by it's ID and holding some metadata.
 */
class Space implements \JsonSerializable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Locale[]
     */
    private $locales = [];

    /**
     * @var Locale
     */
    private $defaultLocale;

    /**
     * @var SystemProperties
     */
    private $sys;

    /**
     * Space constructor.
     *
     * @param string           $name     Name of this space.
     * @param Locale[]         $locales  Locales supported by this space.
     * @param SystemProperties $sys      Metadata for this space.
     */
    public function __construct($name, array $locales, SystemProperties $sys)
    {
        $this->name = $name;
        $this->locales = $locales;

        foreach ($locales as $locale) {
            if ($locale->isDefault()) {
                $this->defaultLocale = $locale;
                break;
            }
        }
        $this->sys = $sys;
    }

    /**
     * Returns the name of this space.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the list of all locales supported by this Space.
     *
     * @return Locale[]
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * Returns the default locale for this space.
     *
     * @return Locale
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    /**
     * Returns the id of this space.
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->sys->getId();
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
            'sys' => $this->sys,
            'name' => $this->name,
            'locales' => $this->locales
        ];
    }
}

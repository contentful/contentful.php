<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Resource;

use Contentful\Delivery\SystemProperties;

/**
 * The Space class represents a single space identified by it's ID and holding some metadata.
 */
class Space extends BaseResource
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var Locale[]
     */
    protected $locales = [];

    /**
     * Space constructor.
     *
     * @param string           $name    name of this space
     * @param Locale[]         $locales locales supported by this space
     * @param SystemProperties $sys     metadata for this space
     */
    public function __construct($name, array $locales, SystemProperties $sys)
    {
        $this->name = $name;
        $this->locales = $locales;

        foreach ($locales as $locale) {
            $this->localesMap[$locale->getCode()] = $locale;
            if ($locale->isDefault()) {
                $this->defaultLocale = $locale;
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
     * @return Locale[]
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * @param string $code Code of the locale to fetch the object for
     *
     * @throws \InvalidArgumentException When no locale with the given code exists
     *
     * @return Locale
     */
    public function getLocale($code)
    {
        foreach ($this->locales as $locale) {
            if ($locale->getCode() === $code) {
                return $locale;
            }
        }

        throw new \InvalidArgumentException(\sprintf(
            'No locale with code "%s" exists in this environment.',
            $code
        ));
    }

    /**
     * Returns the default locale for this space.
     *
     * @throws \RuntimeException
     *
     * @return Locale
     */
    public function getDefaultLocale()
    {
        foreach ($this->locales as $locale) {
            if ($locale->isDefault()) {
                return $locale;
            }
        }

        throw new \RuntimeException('No locale marked as default exists in this environment.');
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
            'locales' => $this->locales,
        ];
    }
}

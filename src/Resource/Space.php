<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Resource;

/**
 * The Space class represents a single space identified by its ID and holding some metadata.
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
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'sys' => $this->sys,
            'name' => $this->name,
            'locales' => $this->locales,
        ];
    }
}

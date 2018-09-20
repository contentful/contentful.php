<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Resource;

class Environment extends BaseResource
{
    /**
     * @var Locale[]
     */
    protected $locales = [];

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
            'locales' => $this->locales,
        ];
    }
}

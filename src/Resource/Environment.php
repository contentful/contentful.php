<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Resource;

use Contentful\Delivery\SystemProperties\Environment as SystemProperties;

class Environment extends BaseResource
{
    /**
     * @var SystemProperties
     */
    protected $sys;

    /**
     * @var Locale[]
     */
    protected $locales = [];

    public function getSystemProperties(): SystemProperties
    {
        return $this->sys;
    }

    /**
     * @return Locale[]
     */
    public function getLocales(): array
    {
        return $this->locales;
    }

    /**
     * @param string $code Code of the locale to fetch the object for
     *
     * @throws \InvalidArgumentException When no locale with the given code exists
     */
    public function getLocale(string $code): Locale
    {
        foreach ($this->locales as $locale) {
            if ($locale->getCode() === $code) {
                return $locale;
            }
        }

        throw new \InvalidArgumentException(sprintf('No locale with code "%s" exists in this environment.', $code));
    }

    /**
     * Returns the default locale for this space.
     *
     * @throws \RuntimeException
     */
    public function getDefaultLocale(): Locale
    {
        foreach ($this->locales as $locale) {
            if ($locale->isDefault()) {
                return $locale;
            }
        }

        throw new \RuntimeException('No locale marked as default exists in this environment.');
    }

    public function jsonSerialize(): array
    {
        return [
            'sys' => $this->sys,
            'locales' => $this->locales,
        ];
    }
}

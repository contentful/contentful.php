<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Resource;

use Contentful\Delivery\SystemProperties\LocalizedResource as SystemProperties;

/**
 * A LocalizedResource can store information for multiple locales.
 * The methods in this base class allow switching between the locales.
 *
 * @property SystemProperties $sys
 */
abstract class LocalizedResource extends BaseResource
{
    /**
     * The code of the currently active locale.
     *
     * @var string
     */
    private $localeCode;

    /**
     * List of codes for all the locales available in the space this resource belongs to.
     *
     * @var string[]
     */
    protected $localeCodes = [];

    /**
     * @param Locale[] $locales The locales available in the space this resource belongs to
     */
    public function initLocales(array $locales)
    {
        foreach ($locales as $locale) {
            if ($locale->isDefault()) {
                $this->localeCode = $locale->getCode();
            }

            $this->localeCodes[] = $locale->getCode();
        }

        $locale = $this->sys->getLocale();
        if ($locale) {
            $this->localeCode = $locale;
        }
    }

    /**
     * Set the locale for this instance.
     * All future calls to a getter will return the information for this locale.
     *
     * @param Locale|string $locale The locale code as string or an instance of Locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->localeCode = $this->getLocaleFromInput($locale);

        return $this;
    }

    /**
     * The locale code for the currently set locale.
     * It will be either the default locale if the resource was fetched using "locale=*",
     * or the one that was used in the API request.
     */
    public function getLocale(): string
    {
        return $this->localeCode;
    }

    /**
     * @param Locale|string|null $input
     *
     * @throws \InvalidArgumentException when $locale is not one of the locales supported by the space
     */
    protected function getLocaleFromInput($input = null): string
    {
        if ($input instanceof Locale) {
            $input = $input->getCode();
        }

        if (null === $input) {
            return $this->localeCode;
        }

        if ($this->sys->getLocale() && $input !== $this->sys->getLocale()) {
            throw new \InvalidArgumentException(sprintf('Entry with ID "%s" was built using locale "%s", but now access using locale "%s" is being attempted.', $this->sys->getId(), $this->sys->getLocale(), $input));
        }

        if (!\in_array($input, $this->localeCodes, true)) {
            throw new \InvalidArgumentException(sprintf('Trying to use invalid locale "%s", available locales are "%s".', $input, implode(', ', $this->localeCodes)));
        }

        return $input;
    }

    /**
     * @throws \RuntimeException If we detect an endless loop
     *
     * @return string|null the locale code for which a value can be found, or null if the end of the chain was reached
     */
    protected function walkFallbackChain(array $valueMap, string $localeCode, Environment $environment)
    {
        $loopCounter = 0;
        while (!isset($valueMap[$localeCode])) {
            $localeCode = $environment->getLocale($localeCode)->getFallbackCode();
            if (null === $localeCode) {
                // We reached the end of the fallback chain and there's no value
                return null;
            }

            ++$loopCounter;
            // The number is arbitrary
            if ($loopCounter > 100) {
                throw new \RuntimeException('Possible endless loop when trying to walk the locale fallback chain.');
            }
        }

        return $localeCode;
    }
}

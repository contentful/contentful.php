<?php
/*
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

/**
 * A LocalizedResource can store information for multiple locales. The methods in this base class allow switching between the locales.
 */
abstract class LocalizedResource
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
    protected $availableLocales = [];

    /**
     * LocalizedResource constructor.
     *
     * @param Locale[] $availableLocales the locales available in the space this resource belongs to
     */
    public function __construct(array $availableLocales)
    {
        foreach ($availableLocales as $locale) {
            if ($locale->isDefault()) {
                $this->localeCode = $locale->getCode();
            }
            $this->availableLocales[] = $locale->getCode();
        }
    }

    /**
     * Set the locale for this instance. All future calls to a getter will return the information for this locale.
     *
     * @param Locale|string $locale the locale code as string or an instance of Locale
     *
     * @throws \InvalidArgumentException when $locale is not one of the locales supported by the space
     *
     * @return $this
     *
     * @api
     */
    public function setLocale($locale)
    {
        if ($locale instanceof Locale) {
            $locale = $locale->getCode();
        }

        if (!\in_array($locale, $this->availableLocales, true)) {
            throw new \InvalidArgumentException('Trying to switch to invalid locale '.$locale.'. Available locales are '.\implode(', ', $this->availableLocales).'.');
        }

        $this->localeCode = $locale;

        return $this;
    }

    /**
     * The locale code for the currently set locale.
     *
     * @return string
     *
     * @api
     */
    public function getLocale()
    {
        return $this->localeCode;
    }

    /**
     * @param Locale|string|null $input
     *
     * @throws \InvalidArgumentException when $locale is not one of the locales supported by the space
     *
     * @return string
     *
     * @api
     */
    protected function getLocaleFromInput($input = null)
    {
        if ($input instanceof Locale) {
            $input = $input->getCode();
        }

        if (null === $input) {
            return $this->localeCode;
        }

        if (!\in_array($input, $this->availableLocales, true)) {
            throw new \InvalidArgumentException('Trying to use invalid locale '.$input.'. Available locales are '.\implode(', ', $this->availableLocales).'.');
        }

        return $input;
    }

    /**
     * @param array                      $valueMap
     * @param string                     $localeCode
     * @param \Contentful\Delivery\Space $space
     *
     * @throws \RuntimeException If we detect an endless loop
     *
     * @return string|null The locale code for which a value can be found. null if the end of the chain has been reached.
     */
    protected function loopThroughFallbackChain(array $valueMap, $localeCode, Space $space)
    {
        $loopCounter = 0;
        while (!isset($valueMap[$localeCode])) {
            $localeCode = $space->getLocale($localeCode)->getFallbackCode();
            if (null === $localeCode) {
                // We've reach the end of the fallback chain and there's no value
                return null;
            }
            ++$loopCounter;
            // The number is arbitrary
            if ($loopCounter > 128) {
                throw new \RuntimeException('Possible endless loop when trying to walk the locale fallback chain.');
            }
        }

        return $localeCode;
    }
}

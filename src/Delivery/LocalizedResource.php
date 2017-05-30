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
     * The code of the currently active locale
     *
     * @var string
     */
    private $localeCode;

    /**
     * List of codes for all the locales available in the space this resource belongs to
     *
     * @var string[]
     */
    protected $availableLocales = [];

    /**
     * LocalizedResource constructor.
     *
     * @param Locale[] $availableLocales The locales available in the space this resource belongs to.
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
     * @param  Locale|string $locale The locale code as string or an instance of Locale.
     *
     * @return $this
     *
     * @throws \InvalidArgumentException When $locale is not one of the locales supported by the space.
     *
     * @api
     */
    public function setLocale($locale)
    {
        if ($locale instanceof Locale) {
            $locale = $locale->getCode();
        }

        if (!in_array($locale, $this->availableLocales)) {
            throw new \InvalidArgumentException('Trying to switch to invalid locale ' . $locale . '. Available locales are ' . implode(', ', $this->availableLocales) . '.');
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
     * @param  Locale|string|null $input
     *
     * @return string
     *
     * @throws \InvalidArgumentException When $locale is not one of the locales supported by the space.
     *
     * @api
     */
    protected function getLocaleFromInput($input = null)
    {
        if ($input instanceof Locale) {
            $input = $input->getCode();
        }

        if ($input === null) {
            return $this->localeCode;
        }

        if (!in_array($input, $this->availableLocales)) {
            throw new \InvalidArgumentException('Trying to use invalid locale ' . $input . '. Available locales are ' . implode(', ', $this->availableLocales) . '.');
        }

        return $input;
    }

    /**
     * @param array                      $valueMap
     * @param string                     $localeCode
     * @param \Contentful\Delivery\Space $space
     *
     * @return string|null The locale code for which a value can be found. null if the end of the chain has been reached.
     *
     * @throws \RuntimeException If we detect an endless loop
     */
    protected function loopThroughFallbackChain(array $valueMap, $localeCode, Space $space)
    {
        $loopCounter = 0;
        while (!isset($valueMap[$localeCode])) {
            $localeCode = $space->getLocale($localeCode)->getFallbackCode();
            if ($localeCode === null) {
                // We've reach the end of the fallback chain and there's no value
                return null;
            }
            $loopCounter++;
            // The number is arbitrary
            if ($loopCounter > 128) {
                throw new \RuntimeException('Possible endless loop when trying to walk the locale fallback chain.');
            }
        }

        return $localeCode;
    }
}

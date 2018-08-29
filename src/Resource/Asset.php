<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Resource;

use Contentful\Core\File\FileInterface;

class Asset extends LocalizedResource
{
    /**
     * @var string[]
     */
    protected $title;

    /**
     * @var string[]
     */
    protected $description;

    /**
     * @var FileInterface[]
     */
    protected $file;

    /**
     * Returns the space this asset belongs to.
     *
     * @return Space
     */
    public function getSpace()
    {
        return $this->sys->getSpace();
    }

    /**
     * Returns the environment this asset belongs to.
     *
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->sys->getEnvironment();
    }

    /**
     * @param Locale|string|null $locale
     *
     * @return string|null
     */
    public function getTitle($locale = \null)
    {
        return $this->getProperty('title', $locale);
    }

    /**
     * @param Locale|string|null $locale
     *
     * @return string|null
     */
    public function getDescription($locale = \null)
    {
        return $this->getProperty('description', $locale);
    }

    /**
     * @param Locale|string|null $locale
     *
     * @return FileInterface|null
     */
    public function getFile($locale = \null)
    {
        return $this->getProperty('file', $locale);
    }

    /**
     * @param string             $property
     * @param Locale|string|null $locale
     *
     * @throws \InvalidArgumentException when $locale is not one of the locales supported by the space
     *
     * @return string|FileInterface|null
     */
    private function getProperty($property, $locale = \null)
    {
        $localeCode = $this->getLocaleFromInput($locale);

        // This checks happens after the call to getLocaleFromInput
        // to make sure the Exception for invalid locales is still thrown.
        if (\null === $this->$property) {
            return \null;
        }

        $localeCode = $this->walkFallbackChain($this->$property, $localeCode, $this->sys->getEnvironment());

        return \null === $localeCode ? \null : $this->{$property}[$localeCode];
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $locale = $this->sys->getLocale();
        $asset = [
            'sys' => $this->sys,
            'fields' => [],
        ];

        if (\null !== $this->title) {
            $asset['fields']['title'] = $locale
                ? $this->title[$locale]
                : $this->title;
        }

        if (\null !== $this->description) {
            $asset['fields']['description'] = $locale
                ? $this->description[$locale]
                : $this->description;
        }

        if (\null !== $this->file) {
            $asset['fields']['file'] = $locale
                ? $this->file[$locale]
                : $this->file;
        }

        $asset['fields'] = (object) $asset['fields'];

        return $asset;
    }
}

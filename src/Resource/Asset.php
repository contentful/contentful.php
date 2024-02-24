<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Resource;

use Contentful\Core\File\FileInterface;
use Contentful\Core\Resource\AssetInterface;
use Contentful\Delivery\SystemProperties\Asset as SystemProperties;
use Contentful\Delivery\SystemProperties\Component\TagTrait;

class Asset extends LocalizedResource implements AssetInterface
{
    use TagTrait;

    /**
     * @var SystemProperties
     */
    protected $sys;

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

    public function getSystemProperties(): SystemProperties
    {
        return $this->sys;
    }

    /**
     * Returns the space this asset belongs to.
     */
    public function getSpace(): Space
    {
        return $this->sys->getSpace();
    }

    /**
     * Returns the environment this asset belongs to.
     */
    public function getEnvironment(): Environment
    {
        return $this->sys->getEnvironment();
    }

    /**
     * @param Locale|string|null $locale
     *
     * @return string|null
     */
    public function getTitle($locale = null)
    {
        /** @var string|null $title */
        $title = $this->getProperty('title', $locale);

        return $title;
    }

    /**
     * @param Locale|string|null $locale
     *
     * @return string|null
     */
    public function getDescription($locale = null)
    {
        /** @var string|null $description */
        $description = $this->getProperty('description', $locale);

        return $description;
    }

    /**
     * @param Locale|string|null $locale
     *
     * @return FileInterface|null
     */
    public function getFile($locale = null)
    {
        /** @var FileInterface|null $file */
        $file = $this->getProperty('file', $locale);

        return $file;
    }

    /**
     * @param Locale|string|null $locale
     *
     * @throws \InvalidArgumentException when $locale is not one of the locales supported by the space
     *
     * @return string|FileInterface|null
     */
    private function getProperty(string $property, $locale = null)
    {
        $localeCode = $this->getLocaleFromInput($locale);

        // This checks happens after the call to getLocaleFromInput
        // to make sure the Exception for invalid locales is still thrown.
        if (null === $this->$property) {
            return null;
        }

        $localeCode = $this->walkFallbackChain($this->$property, $localeCode, $this->sys->getEnvironment());

        return null === $localeCode
            ? null
            : $this->{$property}[$localeCode];
    }

    public function jsonSerialize(): array
    {
        $locale = $this->sys->getLocale();
        $asset = [
            'sys' => $this->sys,
            'fields' => [],
        ];

        if (null !== $this->title) {
            $asset['fields']['title'] = $locale
                ? $this->title[$locale]
                : $this->title;
        }

        if (null !== $this->description) {
            $asset['fields']['description'] = $locale
                ? $this->description[$locale]
                : $this->description;
        }

        if (null !== $this->file) {
            $asset['fields']['file'] = $locale
                ? $this->file[$locale]
                : $this->file;
        }

        $asset['fields'] = (object) $asset['fields'];

        return $asset;
    }
}

<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Resource;

use Contentful\Core\Api\DateTimeImmutable;
use Contentful\Core\File\FileInterface;
use Contentful\Delivery\SystemProperties;

class Asset extends LocalizedResource
{
    /**
     * @var array
     */
    protected $title;

    /**
     * @var array
     */
    protected $description;

    /**
     * @var array
     */
    protected $file;

    /**
     * Asset constructor.
     *
     * @param array            $title
     * @param array            $description
     * @param array            $file
     * @param SystemProperties $sys
     */
    public function __construct($title, $description, $file, SystemProperties $sys)
    {
        parent::__construct($sys->getSpace()->getLocales());

        $this->title = $title;
        $this->description = $description;
        $this->file = $file;
        $this->sys = $sys;
    }

    /**
     * @param Locale|string|null $locale
     *
     * @return string|null
     */
    public function getTitle($locale = null)
    {
        return $this->getProperty('title', $locale);
    }

    /**
     * @param Locale|string|null $locale
     *
     * @return string|null
     */
    public function getDescription($locale = null)
    {
        return $this->getProperty('description', $locale);
    }

    /**
     * @param Locale|string|null $locale
     *
     * @return FileInterface|null
     */
    public function getFile($locale = null)
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
    private function getProperty($property, $locale = null)
    {
        $localeCode = $this->getLocaleFromInput($locale);

        // This checks happens after the call to getLocaleFromInput
        // to make sure the Exception for invalid locales is still thrown.
        if (null === $this->$property) {
            return null;
        }

        $localeCode = $this->loopThroughFallbackChain($this->$property, $localeCode, $this->sys->getSpace());

        return null === $localeCode ? null : $this->{$property}[$localeCode];
    }

    /**
     * Returns the Revision of this Asset.
     *
     * @return int
     */
    public function getRevision()
    {
        return $this->sys->getRevision();
    }

    /**
     * Returns the time when this Asset was last changed.
     *
     * @return DateTimeImmutable
     */
    public function getUpdatedAt()
    {
        return $this->sys->getUpdatedAt();
    }

    /**
     * Returns the time when this Asset was created.
     *
     * @return DateTimeImmutable
     */
    public function getCreatedAt()
    {
        return $this->sys->getCreatedAt();
    }

    /**
     * Returns the Space this Asset belongs to.
     *
     * @return Space
     */
    public function getSpace()
    {
        return $this->sys->getSpace();
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
        $entryLocale = $this->sys->getLocale();

        $obj = (object) [
            'fields' => (object) [],
            'sys' => $this->sys,
        ];
        if (null !== $this->file) {
            if ($entryLocale) {
                $obj->fields->file = $this->file[$entryLocale];
            } else {
                $obj->fields->file = $this->file;
            }
        }

        if (null !== $this->title) {
            if ($entryLocale) {
                $obj->fields->title = $this->title[$entryLocale];
            } else {
                $obj->fields->title = $this->title;
            }
        }

        if (null !== $this->description) {
            if ($entryLocale) {
                $obj->fields->description = $this->description[$entryLocale];
            } else {
                $obj->fields->description = $this->description;
            }
        }

        return $obj;
    }
}

<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

use Contentful\File\FileInterface;

class Asset extends LocalizedResource implements \JsonSerializable
{
    /**
     * @var array
     */
    private $title;

    /**
     * @var array
     */
    private $description;

    /**
     * @var array
     */
    private $file;

    /**
     * @var SystemProperties
     */
    private $sys;

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
     * The title of the asset.
     *
     * @param  Locale|string|null $locale
     *
     * @return string|null
     *
     * @throws \InvalidArgumentException When $locale is not one of the locales supported by the space.
     */
    public function getTitle($locale = null)
    {
        $localeCode = $this->getLocaleFromInput($locale);

        // This checks happens after the call to getLocaleFromInput to make sure the Exception for invalid locales is still thrown.
        if ($this->title === null) {
            return null;
        }

        $localeCode = $this->loopThroughFallbackChain($this->title, $localeCode, $this->getSpace());

        return $localeCode === null ? null : $this->title[$localeCode];
    }

    /**
     * @param  Locale|string|null $locale
     *
     * @return string|null
     *
     * @throws \InvalidArgumentException When $locale is not one of the locales supported by the space.
     */
    public function getDescription($locale = null)
    {
        $localeCode = $this->getLocaleFromInput($locale);

        // This checks happens after the call to getLocaleFromInput to make sure the Exception for invalid locales is still thrown.
        if ($this->description === null) {
            return null;
        }

        $localeCode = $this->loopThroughFallbackChain($this->description, $localeCode, $this->getSpace());

        return $localeCode === null ? null : $this->description[$localeCode];
    }

    /**
     * @param  Locale|string|null $locale
     *
     * @return FileInterface
     *
     * @throws \InvalidArgumentException When $locale is not one of the locales supported by the space.
     */
    public function getFile($locale = null)
    {
        $localeCode = $this->getLocaleFromInput($locale);

        // This checks happens after the call to getLocaleFromInput to make sure the Exception for invalid locales is still thrown.
        if ($this->file === null) {
            return null;
        }

        $localeCode = $this->loopThroughFallbackChain($this->file, $localeCode, $this->getSpace());

        return $localeCode === null ? null : $this->file[$localeCode];
    }

    /**
     * Returns the ID of this Asset.
     *
     * @return string
     *
     * @api
     */
    public function getId()
    {
        return $this->sys->getId();
    }

    /**
     * Returns the Revision of this Asset.
     *
     * @return int
     *
     * @api
     */
    public function getRevision()
    {
        return $this->sys->getRevision();
    }

    /**
     * Returns the time when this Asset was last changed.
     *
     * @return \DateTimeImmutable
     *
     * @api
     */
    public function getUpdatedAt()
    {
        return $this->sys->getUpdatedAt();
    }

    /**
     * Returns the time when this Asset was created.
     *
     * @return \DateTimeImmutable
     *
     * @api
     */
    public function getCreatedAt()
    {
        return $this->sys->getCreatedAt();
    }

    /**
     * Returns the Space this Asset belongs to.
     *
     * @return Space
     *
     * @api
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
     *
     * @api
     */
    public function jsonSerialize()
    {
        $entryLocale = $this->sys->getLocale();

        $obj = (object) [
            'fields' => (object) [],
            'sys' => $this->sys
        ];
        if ($this->file !== null) {
            if ($entryLocale) {
                $obj->fields->file = $this->file[$entryLocale];
            } else {
                $obj->fields->file = $this->file;
            }
        }

        if ($this->title !== null) {
            if ($entryLocale) {
                $obj->fields->title = $this->title[$entryLocale];
            } else {
                $obj->fields->title = $this->title;
            }
        }

        if ($this->description !== null) {
            if ($entryLocale) {
                $obj->fields->description = $this->description[$entryLocale];
            } else {
                $obj->fields->description = $this->description;
            }
        }

        return $obj;
    }
}

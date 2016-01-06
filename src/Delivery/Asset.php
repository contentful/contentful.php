<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

class Asset extends LocalizedResource implements \JsonSerializable
{
    /**
     * @var object
     */
    private $title;

    /**
     * @var object
     */
    private $description;

    /**
     * @var object
     */
    private $file;

    /**
     * @var SystemProperties
     */
    private $sys;

    /**
     * Asset constructor.
     *
     * @param object           $title
     * @param object           $description
     * @param object           $file
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
     * @return string
     */
    public function getTitle($locale = null)
    {
        $localeCode = $this->getLocaleFromInput($locale);

        return $this->title->$localeCode;
    }

    /**
     * @param  Locale|string|null $locale
     *
     * @return string
     */
    public function getDescription($locale = null)
    {
        $localeCode = $this->getLocaleFromInput($locale);

        // This checks happens after the call to getLocaleFromInput to make sure the Exception for invalid locales is still thrown.
        if ($this->description === null) {
            return null;
        }

        return $this->description->$localeCode;
    }

    /**
     * @param  Locale|string|null $locale
     *
     * @return File
     */
    public function getFile($locale = null)
    {
        $localeCode = $this->getLocaleFromInput($locale);

        return $this->file->$localeCode;
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
        $obj = (object) [
            'fields' => (object) [
                'title' => $this->title,
                'file' => $this->file
            ],
            'sys' => $this->sys
        ];

        if ($this->description !== null) {
            $obj->fields->description = $this->description;
        }

        return $obj;
    }
}

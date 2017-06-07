<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\File;

class ImageFile extends File
{
    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * ImageFile constructor.
     *
     * @param string $fileName
     * @param string $contentType
     * @param string $url
     * @param int    $size
     * @param int    $width
     * @param int    $height
     */
    public function __construct($fileName, $contentType, $url, $size, $width, $height)
    {
        parent::__construct($fileName, $contentType, $url, $size);

        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Returns the width of the image.
     *
     * @return int
     *
     * @api
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Returns the height of the image.
     *
     * @return int
     *
     * @api
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param ImageOptions|null $options
     *
     * @return string
     *
     * @api
     */
    public function getUrl(ImageOptions $options = null)
    {
        $query = $options !== null ? '?' . $options->getQueryString() : '';

        return parent::getUrl() . $query;
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
        $obj = parent::jsonSerialize();
        $obj->details->image = (object) [
            'width' => $this->width,
            'height' => $this->height
        ];

        return $obj;
    }
}

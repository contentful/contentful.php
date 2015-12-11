<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

/**
 * ImageOptions allows specifying extended options to the Contentful Image API .
 *
 * to resize images or change their format.
 *
 *
 * @see https://www.contentful.com/developers/docs/references/images-api/#/reference Image API Reference
 * @see \Contentful\Delivery\ImageFile ImageFile class
 * @api
 */
class ImageOptions
{
    /**
     * @var int|null
     */
    private $width;

    /**
     * @var int|null
     */
    private $height;

    /**
     * @var string|null
     */
    private $format;

    /**
     * @var int|null
     */
    private $quality;

    /**
     * @var bool
     */
    private $progressive = false;

    /**
     * ImageOptions constructor.
     *
     * Empty, only included for forward compatibility.
     */
    public function __construct()
    {
    }

    /**
     * The urlencoded query string for these options.
     *
     * @return string
     *
     * @api
     */
    public function getQueryString()
    {
        $options = [
            'w' => $this->width,
            'h' => $this->height,
            'fm' => $this->format,
            'q' => $this->quality
        ];

        if ($this->quality !== null || $this->progressive) {
            $options['fm'] = 'jpg';
        }
        if ($this->progressive) {
            $options['fl'] = 'progressive';
        }

        return http_build_query($options, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Get the width of the image. Will be null if no width is set.
     *
     * @return int|null
     *
     * @api
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set the width of the image.
     *
     * The image will by default not be stretched, skewed or enlarged. Instead it will be fit into the bounding box given
     * by the width and height parameters.
     *
     * Can be set to null to not set a width.
     *
     * @param  int|null $width The width in pixel.
     *
     * @return $this
     *
     * @throws \InvalidArgumentException If $width is negative
     *
     * @api
     */
    public function setWidth($width = null)
    {
        if ($width !== null && $width < 0) {
            throw new \InvalidArgumentException('Width must not be negative');
        }

        $this->width = $width;

        return $this;
    }

    /**
     * Get the height of the image. Will be null if no height is set.
     *
     * @return int|null
     *
     * @api
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set the height of the image.
     *
     * The image will by default not be stretched, skewed or enlarged. Instead it will be fit into the bounding box given
     * by the width and height parameters.
     *
     * Can be set to null to not set a height.
     *
     * @param  int|null $height The height in pixel.
     *
     * @return $this
     *
     * @throws \InvalidArgumentException If $height is negative
     *
     * @api
     */
    public function setHeight($height = null)
    {
        if ($height !== null && $height < 0) {
            throw new \InvalidArgumentException('Height must not be negative');
        }

        $this->height = $height;

        return $this;
    }

    /**
     * Format of the image. Possible values are "png" and "jpg". Will be null if no format is set.
     *
     * @return string|null
     *
     * @api
     */
    public function getFormat()
    {
        if ($this->quality !== null || $this->progressive) {
            return 'jpg';
        }

        return $this->format;
    }

    /**
     * Set the format of the image. Valid values are "png" and "jpg". Can be set to null to not enforce a format.
     *
     * @param  string|null $format
     *
     * @return $this
     *
     * @throws \InvalidArgumentException If $format is not a valid value
     *
     * @api
     */
    public function setFormat($format = null)
    {
        $validValues = ['png', 'jpg'];

        if ($format !== null && !in_array($format, $validValues)) {
            throw new \InvalidArgumentException('Unknown format "' . $format . '" given. Expected "png", "jpg" or null');
        }

        $this->format = $format;

        return $this;
    }

    /**
     * Quality of the JPEG encoded image. Will be null if no quality is set.
     *
     * @return int|null If an int, must be between 1 and 100.
     *
     * @api
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * Quality of the JPEG encoded image.
     *
     * The image format will be forced to JPEG.
     *
     * @param  int|null $quality If an int, between 1 and 100.
     *
     * @return $this
     *
     * @throws \InvalidArgumentException If $quality is out of range
     *
     * @api
     */
    public function setQuality($quality = null)
    {
        if ($quality !== null && ($quality < 1 || $quality > 100)) {
            throw new \InvalidArgumentException('$quality has to be between 1 and 100, ' . $quality . ' given.');
        }

        $this->quality = $quality;

        return $this;
    }

    /**
     * Returns true if the image will be loaded as progressive JPEG.
     *
     * @return bool
     *
     * @bool
     */
    public function isProgressive()
    {
        return $this->progressive;
    }

    /**
     * Set to true to load the image as a progressive JPEG.
     *
     * The image format will be forced to JPEG.
     *
     * @param  bool|null $progressive
     *
     * @return $this
     */
    public function setProgressive($progressive = null)
    {
        $this->progressive = (bool) $progressive;

        return $this;
    }
}

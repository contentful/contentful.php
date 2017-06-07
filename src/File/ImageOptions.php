<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\File;

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
     * @var string|null
     */
    private $resizeFit;

    /**
     * @var string|null
     */
    private $resizeFocus;

    /**
     * @var float|null
     */
    private $radius;

    /**
     * @var string|null
     */
    private $backgroundColor;

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
            'q' => $this->quality,
            'r' => $this->radius
        ];

        if ($this->quality !== null || $this->progressive) {
            $options['fm'] = 'jpg';
        }
        if ($this->progressive) {
            $options['fl'] = 'progressive';
        }
        if ($this->resizeFit !== null) {
            $options['fit'] = $this->resizeFit;

            if ($this->resizeFit === 'thumb' && $this->resizeFocus !== null) {
                $options['f'] = $this->resizeFocus;
            }
            if ($this->resizeFit === 'pad' && $this->backgroundColor !== null) {
                $options['bg'] = 'rgb:' . substr($this->backgroundColor, 1);
            }
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
        $validValues = ['png', 'jpg', 'webp'];

        if ($format !== null && !in_array($format, $validValues, true)) {
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
     * @api
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
     *
     * @api
     */
    public function setProgressive($progressive = null)
    {
        $this->progressive = (bool) $progressive;

        return $this;
    }

    /**
     * Returns the behavior used for resizing images.
     *
     * @return null|string
     *
     * @api
     */
    public function getResizeFit()
    {
        return $this->resizeFit;
    }

    /**
     * Change the behavior when resizing the image.
     *
     * By default, images are resized to fit inside the bounding box set trough setWidth and setHeight while retaining
     * their aspect ratio.
     *
     * Possible values are:
     * - null for the default value
     * - 'pad' Same as the default, but add padding so that the generated image has exactly the given dimensions.
     * - 'crop' Crop a part of the original image.
     * - 'fill' Fill the given dimensions by cropping the image.
     * - 'thumb' Create a thumbnail of detected faces from image, used with 'setFocus'.
     * - 'scale' Scale the image regardless of the original aspect ratio.
     *
     * @param  string|null $resizeFit
     *
     * @return $this
     *
     * @throws \InvalidArgumentException For unknown values of $resizeBehavior
     *
     * @api
     */
    public function setResizeFit($resizeFit = null)
    {
        $validValues = ['pad', 'crop', 'fill', 'thumb', 'scale'];

        if ($resizeFit !== null && !in_array($resizeFit, $validValues, true)) {
            throw new \InvalidArgumentException('Unknown resize behavior "' . $resizeFit . '" given. Expected "pad", "crop", "fill", "thumb", "scale" or null');
        }

        $this->resizeFit = $resizeFit;

        return $this;
    }

    /**
     * Get the focus area for resizing.
     *
     * @return string|null
     *
     * @api
     */
    public function getResizeFocus()
    {
        return $this->resizeFocus;
    }

    /**
     * Set the focus area when the resize fit is set to 'thumb'.
     *
     * Possible values are:
     * - 'top', 'right', 'left', 'bottom'
     * - A combination like 'bottom_right'
     * - 'face' or 'faces' to focus the resizing via face detection
     *
     * @param  string|null $resizeFocus
     *
     * @return $this
     *
     * @throws \InvalidArgumentException For unknown values of $resizeFocus
     *
     * @api
     */
    public function setResizeFocus($resizeFocus = null)
    {
        $validValues = ['face', 'faces', 'top', 'bottom', 'right', 'left', 'top_right', 'top_left', 'bottom_right', 'bottom_left'];

        if ($resizeFocus !== null && !in_array($resizeFocus, $validValues, true)) {
            throw new \InvalidArgumentException('Unknown resize focus "' . $resizeFocus . '" given."');
        }

        $this->resizeFocus = $resizeFocus;

        return $this;
    }

    /**
     * Radius used to crop the image.
     *
     * @return float|null
     *
     * @api
     */
    public function getRadius()
    {
        return $this->radius;
    }

    /**
     * Add rounded corners to your image or crop to a circle/ellipsis
     *
     * @param  float|null $radius A float value defining the corner radius.
     *
     * @return $this
     *
     * @throws \InvalidArgumentException If $radius is negative
     *
     * @api
     */
    public function setRadius($radius = null)
    {
        if ($radius !== null && $radius < 0) {
            throw new \InvalidArgumentException('Radius must not be negative');
        }

        $this->radius = $radius;

        return $this;
    }

    /**
     * Returns the background color used when padding an image.
     *
     * @return string|null
     *
     * @api
     */
    public function getBackgroundColor()
    {
        return $this->backgroundColor;
    }

    /**
     * Background color, relevant if the resize fit type 'pad' is used.
     *
     * Expects a valid hexadecimal HTML color like '#9090ff'. Default is transparency.
     *
     * @param  string|null $backgroundColor
     *
     * @return $this
     *
     * @throws \InvalidArgumentException If the $backgroundColor is not in hexadecimal format.
     *
     * @api
     */
    public function setBackgroundColor($backgroundColor = null)
    {
        if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $backgroundColor)) {
            throw new \InvalidArgumentException('Background color must be in hexadecimal format.');
        }

        $this->backgroundColor = $backgroundColor;

        return $this;
    }
}

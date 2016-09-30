<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful;

/**
 * The Location class encodes a geographic Location based on latitude and longitude.
 *
 * @api
 */
class Location implements \JsonSerializable
{
    /**
     * @var float
     */
    private $latitude;

    /**
     * @var float
     */
    private $longitude;

    /**
     * @param float $latitude
     * @param float $longitude
     *
     * @api
     */
    public function __construct($latitude, $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * Returns the latitude.
     *
     * @return float
     *
     * @api
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Returns the longitude
     *
     * @return float
     *
     * @api
     */
    public function getLongitude()
    {
        return $this->longitude;
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
        return (object) ['lat' => $this->latitude, 'long' => $this->longitude];
    }

    /**
     * Format the encoded value as required by the Contentful API.
     *
     * @return string
     *
     * @api
     */
    public function queryStringFormatted()
    {
        return $this->latitude . ',' . $this->longitude;
    }
}

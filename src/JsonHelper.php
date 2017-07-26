<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful;

/**
 * Helper methods for handling JSON encoding/decoding
 */
class JsonHelper
{
    /**
     * @param  string $json JSON encoded object or array
     *
     * @return array
     *
     * @throws \RuntimeException On invalid JSON
     *
     * @deprecated 2.2 Use \GuzzleHttp\json_decode() instead
     *
     * @see \GuzzleHttp\json_decode()
     */
    public static function decode($json)
    {
        try {
            return \GuzzleHttp\json_decode($json, true);
        } catch (\InvalidArgumentException $e) {
            throw new \RuntimeException(json_last_error_msg(), json_last_error());
        }
    }

    /**
     * @param  object|array $value
     *
     * @return string
     *
     * @throws \RuntimeException When the encoding failed
     *
     * @deprecated 2.2 Use \GuzzleHttp\json_encode() instead
     *
     * @see \GuzzleHttp\json_encode()
     */
    public static function encode($value)
    {
        try {
            return \GuzzleHttp\json_encode($value, JSON_UNESCAPED_UNICODE);
        } catch (\InvalidArgumentException $e) {
            throw new \RuntimeException(json_last_error_msg(), json_last_error());
        }
    }
}

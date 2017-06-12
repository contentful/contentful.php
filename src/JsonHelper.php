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
     */
    public static function decode($json)
    {
        $result = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(json_last_error_msg(), json_last_error());
        }

        return $result;
    }

    /**
     * @param  object|array $value
     *
     * @return string
     *
     * @throws \RuntimeException When the encoding failed
     */
    public static function encode($value)
    {
        $result = json_encode($value, JSON_UNESCAPED_UNICODE);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(json_last_error_msg(), json_last_error());
        }

        return $result;
    }
}

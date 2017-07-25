<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful;

class DateHelper
{
    /**
     * Unfortunately PHP has no easy way to create a nice, ISO 8601 formatted date string with milliseconds and Z
     * as the time zone specifier. Thus this hack.
     *
     * @param  \DateTimeImmutable $date
     *
     * @return string ISO 8601 formatted date
     *
     * @deprecated 2.2 Use Contentful\format_date_for_json($date) instead
     *
     * @see Contentful\format_date_for_json()
     */
    public static function formatForJson(\DateTimeImmutable $date)
    {
        return format_date_for_json($date);
    }
}

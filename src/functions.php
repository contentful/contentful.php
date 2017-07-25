<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful;

/**
  * Unfortunately PHP has no easy way to create a nice, ISO 8601 formatted date string with milliseconds and Z
  * as the time zone specifier. Thus this hack.
  *
  * @param \DateTimeImmutable $date
  *
  * @return string ISO 8601 formatted date
  */
 function format_date_for_json(\DateTimeImmutable $date)
 {
     $date = $date->setTimezone(new \DateTimeZone('Etc/UTC'));
     $result = $date->format('Y-m-d\TH:i:s') ;
     $milliseconds = floor($date->format('u')/1000);
     if ($milliseconds > 0) {
         $result .= '.' . str_pad($milliseconds, 3, '0', STR_PAD_LEFT);
     }

     return $result . 'Z';
 }

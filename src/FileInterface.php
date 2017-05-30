<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful;

interface FileInterface extends \JsonSerializable
{
    /**
     * The name of this file
     *
     * @return string
     *
     * @api
     */
    public function getFileName();

    /**
     * The Content- (or MIME-)Type of this file.
     *
     * @return string
     *
     * @api
     */
    public function getContentType();
}

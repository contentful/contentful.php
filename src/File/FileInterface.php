<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\File;

interface FileInterface extends \JsonSerializable
{
    /**
     * The name of this file.
     *
     * @return string
     */
    public function getFileName();

    /**
     * The Content- (or MIME-)Type of this file.
     *
     * @return string
     */
    public function getContentType();
}

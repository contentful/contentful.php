<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\File;

use Contentful\Link;

class LocalUploadFile implements UnprocessedFileInterface
{
    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    private $contentType;

    /**
     * @var Link
     */
    private $uploadFrom;

    public function __construct($fileName, $contentType, Link $uploadFrom)
    {
        $this->fileName = $fileName;
        $this->contentType = $contentType;
        $this->uploadFrom = $uploadFrom;
    }

    /**
     * The name of this file
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * The Content- (or MIME-)Type of this file.
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @return Link
     */
    public function getUploadFrom()
    {
        return $this->uploadFrom;
    }

    /**
     * Returns an object to be used by `json_encode` to serialize objects of this class.
     *
     * @return object
     *
     * @see http://php.net/manual/en/jsonserializable.jsonserialize.php JsonSerializable::jsonSerialize
     */
    public function jsonSerialize()
    {
        return (object) [
            'fileName' => $this->fileName,
            'contentType' => $this->contentType,
            'uploadFrom' => $this->uploadFrom
        ];
    }
}

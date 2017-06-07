<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\File;

class File implements FileInterface
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
     * @var string
     */
    private $url;

    /**
     * @var int
     */
    private $size;

    /**
     * File constructor.
     *
     * @param string $fileName
     * @param string $contentType
     * @param string $url
     * @param int    $size         Size in bytes
     */
    public function __construct($fileName, $contentType, $url, $size)
    {
        $this->fileName = $fileName;
        $this->contentType = $contentType;
        $this->url = $url;
        $this->size = $size;
    }

    /**
     * The name of this file
     *
     * @return string
     *
     * @api
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * The Content- (or MIME-)Type of this file.
     *
     * @return string
     *
     * @api
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * The Url where this file can be retrieved.
     *
     * @return string
     *
     * @api
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * The size in bytes of this file.
     *
     * @return int
     *
     * @api
     */
    public function getSize()
    {
        return $this->size;
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
        return (object) [
            'fileName' => $this->fileName,
            'contentType' => $this->contentType,
            'details' => (object) [
                'size' => $this->size
            ],
            'url' => $this->url
        ];
    }
}

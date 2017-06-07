<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\File;

class UploadFile implements FileInterface
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
    private $upload;

    public function __construct($fileName, $contentType, $upload)
    {
        $this->fileName = $fileName;
        $this->contentType = $contentType;
        $this->upload = $upload;
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
     * @param string $fileName
     *
     * @return $this
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
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
     * @param string $contentType
     *
     * @return $this
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * @return string
     */
    public function getUpload()
    {
        return $this->upload;
    }

    /**
     * @param string $upload
     *
     * @return $this
     */
    public function setUpload($upload)
    {
        $this->upload = $upload;

        return $this;
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
            'upload' => $this->upload
        ];
    }
}

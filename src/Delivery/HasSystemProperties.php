<?php

namespace Contentful\Delivery;

/**
 * A trait for classes that have a "sys" property in some form. It provides some wrapper methods around the underlying
 * SystemProperties object
 *
 * @package Contentful\Delivery
 */
trait HasSystemProperties
{

    /**
     * @var SystemProperties
     */
    private $sys;


    /**
     * Returns the ID of the resource.
     *
     * @return string
     *
     * @api
     */
    public function getId()
    {
        return $this->sys->getId();
    }


    /**
     * Returns the space the resource used to belong to.
     *
     * @return \Contentful\Delivery\Space
     *
     * @api
     */
    public function getSpace()
    {
        return $this->sys->getSpace();
    }


    /**
     * Returns the last revision of the resource before it was deleted.
     *
     * @return int
     *
     * @api
     */
    public function getRevision()
    {
        return $this->sys->getRevision();
    }


    /**
     * Returns the time when the resource was updated.
     *
     * @return \DateTimeImmutable
     *
     * @api
     */
    public function getUpdatedAt()
    {
        return $this->sys->getUpdatedAt();
    }


    /**
     * Returns the time when the resource was created.
     *
     * @return \DateTimeImmutable
     *
     * @api
     */
    public function getCreatedAt()
    {
        return $this->sys->getCreatedAt();
    }


    /**
     * Returns the time when the resource was deleted.
     *
     * @return \DateTimeImmutable
     *
     * @api
     */
    public function getDeletedAt()
    {
        return $this->sys->getDeletedAt();
    }


    /**
     * @return ContentType|null
     */
    public function getContentType()
    {
        return $this->sys->getContentType();
    }

}

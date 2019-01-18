<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2019 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\SystemProperties;

use Contentful\Core\Resource\SystemPropertiesInterface;

/**
 * A SystemProperties instance contains the metadata of a resource.
 */
abstract class BaseSystemProperties implements SystemPropertiesInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $type;

    /**
     * SystemProperties constructor.
     *
     * @param array $sys
     */
    public function __construct(array $sys)
    {
        $this->id = $sys['id'];
        $this->type = $sys['type'];
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
        ];
    }
}

<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2019 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\SystemProperties\Component;

use Contentful\Delivery\Resource\Space;

trait SpaceTrait
{
    /**
     * @var Space
     */
    protected $space;

    /**
     * @param array $data
     */
    protected function initSpace(array $data)
    {
        $this->space = $data['space'];
    }

    /**
     * @return array
     */
    protected function jsonSerializeSpace(): array
    {
        return [
            'space' => $this->space->asLink(),
        ];
    }

    /**
     * @return Space
     */
    public function getSpace(): Space
    {
        return $this->space;
    }
}

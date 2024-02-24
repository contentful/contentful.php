<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
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

    protected function initSpace(array $data)
    {
        $this->space = $data['space'];
    }

    protected function jsonSerializeSpace(): array
    {
        return [
            'space' => $this->space->asLink(),
        ];
    }

    public function getSpace(): Space
    {
        return $this->space;
    }
}

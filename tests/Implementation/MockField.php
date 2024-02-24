<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Implementation;

use Contentful\Delivery\Resource\ContentType\Field;

class MockField extends Field
{
    /**
     * MockField constructor.
     */
    public function __construct(string $id, string $name, string $type, array $data = [])
    {
        parent::__construct($id, $name, $type);

        foreach ($data as $property => $value) {
            if (property_exists($this, $property)) {
                $this->$property = $value;
            }
        }
    }
}

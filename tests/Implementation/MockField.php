<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Implementation;

use Contentful\Delivery\Resource\ContentType\Field;

class MockField extends Field
{
    public function __construct($id, $name, $type, array $data = [])
    {
        parent::__construct($id, $name, $type);

        foreach ($data as $property => $value) {
            if (\property_exists($this, $property)) {
                $this->$property = $value;
            }
        }
    }
}

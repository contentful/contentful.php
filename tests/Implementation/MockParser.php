<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Implementation;

use Contentful\StructuredText\Node\NodeInterface;
use Contentful\StructuredText\Node\Text;
use Contentful\StructuredText\ParserInterface;

class MockParser implements ParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse(array $data): NodeInterface
    {
        return new Text('Some text');
    }

    /**
     * {@inheritdoc}
     */
    public function parseCollection(array $data): array
    {
        return \array_map([$this, 'parse'], $data);
    }
}

<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Implementation;

use Contentful\RichText\Node\NodeInterface;
use Contentful\RichText\Node\Text;
use Contentful\RichText\ParserInterface;

class MockParser implements ParserInterface
{
    public function parse(array $data): NodeInterface
    {
        return new Text('Some text');
    }

    public function parseCollection(array $data): array
    {
        return array_map([$this, 'parse'], $data);
    }

    public function parseLocalized(array $data, ?string $locale): NodeInterface
    {
        return new Text('Some text');
    }

    public function parseCollectionLocalized(array $data, ?string $locale): array
    {
        return array_map([$this, 'parse'], $data);
    }
}

<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Implementation;

use Contentful\Core\Resource\ResourceInterface;
use Contentful\Core\ResourceBuilder\ResourceBuilderInterface;

class MockResourceBuilder implements ResourceBuilderInterface
{
    public function build(array $data, ?ResourceInterface $resource = null)
    {
        if ($resource) {
            return $resource;
        }

        $type = $data['sys']['type'] ?? 'Entry';
        $id = $data['sys']['id'] ?? 'entryId';
        $locale = $data['sys']['locale'] ?? null;

        unset($data['sys']);

        switch ($type) {
            case 'Asset':
                return MockAsset::withSys($id, $data, $locale);
            case 'ContentType':
                return MockContentType::withSys($id, $data);
            case 'Entry':
                return MockEntry::withSys($id, $data, $locale);
            case 'Environment':
                return MockEnvironment::withSys($id, $data);
            case 'Locale':
                return MockLocale::withSys($id, $data);
            case 'Space':
                return MockSpace::withSys($id, $data);
        }
    }
}

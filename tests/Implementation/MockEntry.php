<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Implementation;

use Contentful\Delivery\Client\ClientInterface;
use Contentful\Delivery\Resource\Entry;
use Contentful\Delivery\SystemProperties\Entry as SystemProperties;

class MockEntry extends Entry
{
    /**
     * MockEntry constructor.
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    public static function withSys(string $id = 'entryId', array $data = [], ?string $locale = null): self
    {
        return new static(array_merge($data, [
            'sys' => new SystemProperties([
                'id' => $id,
                'type' => 'Entry',
                'space' => MockSpace::withSys('spaceId'),
                'environment' => MockEnvironment::withSys('environmentId'),
                'contentType' => MockContentType::withSys('contentTypeId'),
                'revision' => 1,
                'createdAt' => '2010-01-01T12:00:00.123Z',
                'updatedAt' => '2010-01-01T12:00:00.123Z',
                'locale' => $locale,
            ]),
        ]));
    }

    public function setClient(?ClientInterface $client = null)
    {
        $this->client = $client;
    }

    /**
     * @return ClientInterface|null
     */
    public function getClient()
    {
        return $this->client;
    }
}

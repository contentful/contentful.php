<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Implementation;

use Contentful\Delivery\Client;
use Contentful\Delivery\Resource\Entry;
use Contentful\Delivery\SystemProperties;

class MockEntry extends Entry
{
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    public static function withSys($id, $data = [])
    {
        return new static(\array_merge($data, [
            'sys' => new SystemProperties([
                'id' => $id,
                'type' => 'Entry',
                'space' => MockSpace::withSys('spaceId'),
                'environment' => MockEnvironment::withSys('environmentId'),
                'contentType' => MockContentType::withSys('contentTypeId'),
                'revision' => 1,
                'createdAt' => '2010-01-01T12:00:00.123Z',
                'updatedAt' => '2010-01-01T12:00:00.123Z',
            ]),
        ]));
    }

    public function setClient(Client $client = \null)
    {
        $this->client = $client;
    }
}

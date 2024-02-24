<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Implementation;

use Contentful\Delivery\Client\JsonDecoderClientInterface;

class JsonDecoderClient implements JsonDecoderClientInterface
{
    public function parseJson(string $json)
    {
        return MockEntry::withSys();
    }

    public function getApi(): string
    {
        return 'DELIVERY';
    }

    public function getSpaceId(): string
    {
        return 'cfexampleapi';
    }

    public function getEnvironmentId(): string
    {
        return 'master';
    }
}

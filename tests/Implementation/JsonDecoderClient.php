<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2020 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Implementation;

use Contentful\Delivery\Client\JsonDecoderClientInterface;

class JsonDecoderClient implements JsonDecoderClientInterface
{
    /**
     * {@inheritdoc}
     */
    public function parseJson(string $json)
    {
        return MockEntry::withSys();
    }

    /**
     * {@inheritdoc}
     */
    public function getApi(): string
    {
        return 'DELIVERY';
    }

    /**
     * {@inheritdoc}
     */
    public function getSpaceId(): string
    {
        return 'cfexampleapi';
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironmentId(): string
    {
        return 'master';
    }
}

<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\E2E;

use Contentful\Core\Resource\ResourceArray;
use Contentful\Delivery\Resource\ContentType;
use Contentful\Delivery\Resource\Environment;
use Contentful\Delivery\Resource\Space;
use Contentful\Tests\Delivery\TestCase;

class ContentTypeTest extends TestCase
{
    /**
     * @vcr content_type_get_all.json
     */
    public function testGetAll()
    {
        $client = $this->getClient('default');

        $contentTypes = $client->getContentTypes();

        $this->assertInstanceOf(ResourceArray::class, $contentTypes);
    }

    /**
     * @vcr content_type_get_one.json
     */
    public function testGetOne()
    {
        $client = $this->getClient('default');

        $contentType = $client->getContentType('cat');

        $this->assertInstanceOf(ContentType::class, $contentType);
        $this->assertSame('cat', $contentType->getId());
        $this->assertInstanceOf(Environment::class, $contentType->getEnvironment());
        $this->assertInstanceOf(Space::class, $contentType->getSpace());
    }
}

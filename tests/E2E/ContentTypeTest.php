<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery\E2E;

use Contentful\Core\Resource\ResourceArray;
use Contentful\Delivery\Resource\ContentType;
use Contentful\Tests\Delivery\TestCase;

class ContentTypeTest extends TestCase
{
    /**
     * @vcr e2e_content_type_get_all.json
     */
    public function testGetAll()
    {
        $client = $this->getClient('cfexampleapi');

        $contentTypes = $client->getContentTypes();

        $this->assertInstanceOf(ResourceArray::class, $contentTypes);
    }

    /**
     * @vcr e2e_content_type_get_one.json
     */
    public function testGetOne()
    {
        $client = $this->getClient('cfexampleapi');

        $contentType = $client->getContentType('cat');

        $this->assertInstanceOf(ContentType::class, $contentType);
        $this->assertSame('cat', $contentType->getId());
    }
}

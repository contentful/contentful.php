<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\ContentType;
use Contentful\ResourceArray;
use Contentful\Tests\Delivery\End2EndTestCase;

class ContentTypeBasicTest extends End2EndTestCase
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

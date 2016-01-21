<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Client;
use Contentful\ResourceArray;
use Contentful\Delivery\ContentType;

class ContentTypeBasicTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client
     */
    private $client;

    public function setUp()
    {
        $this->client = new Client('b4c0n73n7fu1', 'cfexampleapi');
    }

    /**
     * @vcr e2e_content_type_get_all.json
     */
    public function testGetAll()
    {
        $contentTypes = $this->client->getContentTypes();

        $this->assertInstanceOf(ResourceArray::class, $contentTypes);
    }

    /**
     * @vcr e2e_content_type_get_one.json
     */
    public function testGetOne()
    {
        $contentType = $this->client->getContentType('cat');

        $this->assertInstanceOf(ContentType::class, $contentType);
        $this->assertEquals('cat', $contentType->getId());
    }
}

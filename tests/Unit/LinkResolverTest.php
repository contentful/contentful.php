<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Unit;

use Contentful\Core\Api\Link;
use Contentful\Delivery\LinkResolver;
use Contentful\Delivery\Resource\Asset;
use Contentful\Delivery\Resource\ContentType;
use Contentful\Delivery\Resource\Entry;
use Contentful\Delivery\Resource\Environment;
use Contentful\Delivery\Resource\Space;
use Contentful\Tests\Delivery\Implementation\MockClient;
use Contentful\Tests\Delivery\TestCase;

class LinkResolverTest extends TestCase
{
    public function testLinksAreResolved()
    {
        $spaceId = \bin2hex(\random_bytes(5));
        $environmentId = \bin2hex(\random_bytes(5));
        $linkResolver = new LinkResolver(new MockClient($spaceId, $environmentId));

        $resourceId = \bin2hex(\random_bytes(5));
        /** @var Asset $resource */
        $resource = $linkResolver->resolveLink(new Link($resourceId, 'Asset'), ['locale' => 'it-IT']);
        $this->assertInstanceOf(Asset::class, $resource);
        $this->assertSame($resourceId, $resource->getId());
        $this->assertSame('it-IT', $resource->getSystemProperties()->getLocale());

        $resourceId = \bin2hex(\random_bytes(5));
        /** @var ContentType $resource */
        $resource = $linkResolver->resolveLink(new Link($resourceId, 'ContentType'));
        $this->assertInstanceOf(ContentType::class, $resource);
        $this->assertSame($resourceId, $resource->getId());

        /** @var Environment $resource */
        $resource = $linkResolver->resolveLink(new Link('irrelevant', 'Environment'));
        $this->assertInstanceOf(Environment::class, $resource);
        $this->assertSame($environmentId, $resource->getId());

        $resourceId = \bin2hex(\random_bytes(5));
        /** @var Entry $resource */
        $resource = $linkResolver->resolveLink(new Link($resourceId, 'Entry'), ['locale' => 'it-IT']);
        $this->assertInstanceOf(Entry::class, $resource);
        $this->assertSame($resourceId, $resource->getId());
        $this->assertSame('it-IT', $resource->getSystemProperties()->getLocale());

        /** @var Space $resource */
        $resource = $linkResolver->resolveLink(new Link('irrelevant', 'Space'));
        $this->assertInstanceOf(Space::class, $resource);
        $this->assertSame($spaceId, $resource->getId());
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Trying to resolve link for unknown type "InvalidType".
     */
    public function testInvalidLink()
    {
        $linkResolver = new LinkResolver(new MockClient());

        $linkResolver->resolveLink(new Link('irrelevant', 'InvalidType'));
    }
}

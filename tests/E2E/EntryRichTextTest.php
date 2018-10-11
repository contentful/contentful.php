<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\E2E;

use Contentful\RichText\Node\Document;
use Contentful\RichText\Renderer;
use Contentful\Tests\Delivery\TestCase;

class EntryRichTextTest extends TestCase
{
    /**
     * @vcr e2e_entry_rich_text_renders.json
     */
    public function testRenders()
    {
        $client = $this->getClient('88dyiqcr7go8');
        $entry = $client->getEntry('6Lg2C9H7q06myQS0yaQ82s');

        $renderer = new Renderer();
        $value = $entry->get('content');
        $this->assertInstanceOf(Document::class, $value);

        $result = $renderer->render($value);

        // Fixture is stored using extra whitespace to improve readability
        $fixture = $this->getFixtureContent('rendered.html');
        $fixture = \trim(\preg_replace('/>\s+</', '><', $fixture));

        $this->assertSame($fixture, $result);
    }
}

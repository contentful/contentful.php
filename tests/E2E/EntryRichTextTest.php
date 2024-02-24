<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\E2E;

use Contentful\Delivery\Query;
use Contentful\Delivery\Resource\Entry;
use Contentful\RichText\Node\Document;
use Contentful\RichText\Renderer;
use Contentful\Tests\Delivery\TestCase;

class EntryRichTextTest extends TestCase
{
    /**
     * @vcr entry_rich_text_renders.json
     */
    public function testRenders()
    {
        $client = $this->getClient('new');
        $query = (new Query())
            ->where('sys.id', '4ywnsKDf10xyJwyhur5vDG')
            ->setLimit(1)
        ;
        /** @var Entry $entry */
        $entry = $client->getEntries($query)[0];

        $renderer = new Renderer();
        $value = $entry->get('content');
        $this->assertInstanceOf(Document::class, $value);

        $result = $renderer->render($value);

        // Fixture is stored using extra whitespace to improve readability
        $fixture = $this->getFixtureContent('rendered.html');
        $fixture = trim(preg_replace('/>\s+</', '><', $fixture));

        $this->assertSame($fixture, $result);
    }

    /**
     * @vcr entry_rich_text_self_reference.json
     */
    public function testSelfReference()
    {
        $client = $this->getClient('new');
        $query = (new Query())
            ->where('sys.id', '7ypRwyhY3CIsmvUByCca9i')
        ;

        $entry = $client->getEntries($query)[0];

        $currentDepth = 1;
        while ($currentDepth < 100) {
            $entry = $entry->get('content')->getContent()[1];

            $entry = $entry->getEntry();
            ++$currentDepth;
        }

        $this->assertInstanceOf(Entry::class, $entry);
    }
}

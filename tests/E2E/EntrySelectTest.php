<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery\E2E;

use Contentful\Core\Resource\ResourceArray;
use Contentful\Delivery\Query;
use Contentful\Tests\Delivery\TestCase;

class EntrySelectTest extends TestCase
{
    /**
     * @vcr e2e_entry_select_metadata.json
     */
    public function testSelectOnlyMetadata()
    {
        $client = $this->getClient('cfexampleapi');

        $query = (new Query())
            ->setContentType('cat')
            ->select(['sys'])
            ->where('sys.id', 'nyancat')
            ->setLimit(1);
        $entries = $client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertNull($entries[0]->getName());
        $this->assertNull($entries[0]->getBestFriend());
    }

    /**
     * @vcr e2e_entry_select_one_field.json
     */
    public function testSelectOnlyOneField()
    {
        $client = $this->getClient('cfexampleapi');

        $query = (new Query())
            ->setContentType('cat')
            ->select(['fields.name'])
            ->where('sys.id', 'nyancat')
            ->setLimit(1);
        $entries = $client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertSame('Nyan Cat', $entries[0]->getName());
        $this->assertNull($entries[0]->getBestFriend());
    }
}

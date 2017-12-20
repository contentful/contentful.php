<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Query;
use Contentful\ResourceArray;
use Contentful\Tests\Delivery\End2EndTestCase;

class EntryLocaleTest extends End2EndTestCase
{
    /**
     * @vcr e2e_entry_locale_get_all.json
     */
    public function testGetAll()
    {
        $client = $this->getClient('cfexampleapi');

        $query = (new Query())
            ->setContentType('cat')
            ->setLocale('*');
        $entries = $client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertSame('Nyan Cat', $entries[0]->getName());
    }

    /**
     * @vcr e2e_entry_locale_get_en_us.json
     */
    public function testGetEnUs()
    {
        $client = $this->getClient('cfexampleapi');

        $query = (new Query())
            ->setContentType('cat')
            ->setLocale('en-US');
        $entries = $client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertSame('Nyan Cat', $entries[0]->getName());
    }

    /**
     * @vcr e2e_entry_locale_get_tlh.json
     */
    public function testGetTlh()
    {
        $client = $this->getClient('cfexampleapi');

        $query = (new Query())
            ->setContentType('cat')
            ->setLocale('tlh');
        $entries = $client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertSame('Nyan vIghro\'', $entries[0]->getName());
    }

    /**
     * @vcr e2e_entry_locale_from_client.json
     */
    public function testGetLocaleFromClient()
    {
        $client = $this->getClient('cfexampleapi_tlh');

        $query = (new Query())
            ->setContentType('cat');
        $entries = $client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertSame('Nyan vIghro\'', $entries[0]->getName());
        $this->assertNull($entries[0]->getName('en-US'));
    }

    /**
     * @vcr e2e_entry_locale_lazy_loading.json
     */
    public function testLocaleUsedWithLazyLoading()
    {
        $client = $this->getClient('cfexampleapi');

        $happycat = $client->getEntry('happycat', 'tlh');
        $nyancat = $happycat->getBestFriend();
        $this->assertSame('Nyan vIghro\'', $nyancat->getName());
    }
}

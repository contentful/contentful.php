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
use Contentful\Delivery\Query;
use Contentful\Tests\Delivery\TestCase;

class EntryLocaleTest extends TestCase
{
    /**
     * @vcr entry_locale_get_all.json
     */
    public function testGetAll()
    {
        $client = $this->getClient('default');

        $query = (new Query())
            ->where('sys.id', 'nyancat')
            ->setLocale('*')
        ;
        $entries = $client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertSame('Nyan Cat', $entries[0]->getName());
        $this->assertSame('Nyan Cat', $entries[0]->get('name'));
    }

    /**
     * @vcr entry_locale_get_en_us.json
     */
    public function testGetEnUs()
    {
        $client = $this->getClient('default');

        $query = (new Query())
            ->where('sys.id', 'nyancat')
            ->setLocale('en-US')
        ;
        $entries = $client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertSame('Nyan Cat', $entries[0]->getName());
        $this->assertSame('Nyan Cat', $entries[0]->get('name'));
    }

    /**
     * @vcr entry_locale_get_tlh.json
     */
    public function testGetTlh()
    {
        $client = $this->getClient('default');

        $query = (new Query())
            ->where('sys.id', 'nyancat')
            ->setLocale('tlh')
        ;
        $entries = $client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertSame('Nyan vIghro\'', $entries[0]->getName());
        $this->assertSame('Nyan vIghro\'', $entries[0]->get('name'));
    }

    /**
     * @vcr entry_locale_get_from_client.json
     */
    public function testGetFromClient()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Entry with ID "nyancat" was built using locale "tlh", but now access using locale "en-US" is being attempted.');

        $client = $this->getClient('default_klingon');

        $query = (new Query())
            ->where('sys.id', 'nyancat')
        ;
        $entries = $client->getEntries($query);

        $this->assertInstanceOf(ResourceArray::class, $entries);
        $this->assertSame('Nyan vIghro\'', $entries[0]->get('name'));

        $entries[0]->get('name', 'en-US');
    }

    /**
     * @vcr entry_locale_lazy_loading.json
     */
    public function testLazyLoading()
    {
        $client = $this->getClient('default');

        $happycat = $client->getEntry('happycat', 'tlh');
        $nyancat = $happycat->getBestFriend();
        $this->assertSame('Nyan vIghro\'', $nyancat->getName());
        $this->assertSame('Nyan vIghro\'', $nyancat->get('name'));
    }
}

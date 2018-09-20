<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Unit;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Contentful\Delivery\Client;
use Contentful\Delivery\InstanceRepository;
use Contentful\Tests\Delivery\TestCase;

class InstanceRepositoryTest extends TestCase
{
    public function testCacheKey()
    {
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $client->method('getApi')
            ->willReturn('DELIVERY')
        ;

        $instanceRepository = new InstanceRepository($client, new ArrayCachePool(), 'cfexampleapi', 'master');

        $key = $instanceRepository->generateCacheKey(
            'Entry',
            'entryId',
            '*'
        );
        $this->assertSame('contentful.DELIVERY.cfexampleapi.master.Entry.entryId.__ALL__', $key);

        $key = $instanceRepository->generateCacheKey(
            'Entry',
            'entryId',
            'en-US'
        );
        $this->assertSame('contentful.DELIVERY.cfexampleapi.master.Entry.entryId.en_US', $key);

        $key = $instanceRepository->generateCacheKey(
            'Entry',
            'entry-id-._',
            'en-US'
        );
        $this->assertSame('contentful.DELIVERY.cfexampleapi.master.Entry.entry_id_._.en_US', $key);
    }
}

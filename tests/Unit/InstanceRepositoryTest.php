<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery\Unit;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Contentful\Delivery\Client;
use Contentful\Delivery\InstanceRepository;

class InstanceRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCacheKey()
    {
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $client->method('getApi')
            ->willReturn('DELIVERY');

        $instanceRepository = new InstanceRepository($client, new ArrayCachePool(), false, 'cfexampleapi', 'master');

        $key = $instanceRepository->generateCacheKey(
            'DELIVERY',
            'Entry',
            'entryId',
            '*'
        );
        $this->assertSame('contentful.DELIVERY.cfexampleapi.master.Entry.entryId.__ALL__', $key);

        $key = $instanceRepository->generateCacheKey(
            'DELIVERY',
            'Entry',
            'entryId',
            'en-US'
        );
        $this->assertSame('contentful.DELIVERY.cfexampleapi.master.Entry.entryId.en_US', $key);

        $key = $instanceRepository->generateCacheKey(
            'DELIVERY',
            'Entry',
            'entry-id-._',
            'en-US'
        );
        $this->assertSame('contentful.DELIVERY.cfexampleapi.master.Entry.entry_id_._.en_US', $key);
    }
}

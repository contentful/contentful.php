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
use Contentful\Tests\Delivery\TestCase;

class LowMemoryTest extends TestCase
{
    public function testConsumption()
    {
        if (!$this->runOnApiCoverage()) {
            return;
        }

        $client = $this->getClient('low_memory');
        $client->getSpace();
        $client->getEnvironment();
        $client->getContentType('blogPost');

        $stepSize = 1000;

        // This should load tens of thousands of records,
        // but keep the memory use low and not break the test suite
        $query = (new Query())
            ->setLimit($stepSize)
        ;
        $skip = 0;
        do {
            $query->setSkip($skip);
            $entries = $client->getEntries($query);
            $this->assertGreaterThanOrEqual(0, \count($entries));
            $skip += $stepSize;
        } while ($entries->getTotal() >= $entries->getLimit() + $entries->getSkip());
    }
}

<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Client;
use Contentful\Delivery\ResponseInfo as DeliveryResponseInfo;
use Contentful\Preview\ResponseInfo as PreviewResponseInfo;

class ResponseInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @vcr e2e_response_info_delivery.json
     */
    public function testSavedResponseInfo()
    {
        $client = new Client('b4c0n73n7fu1', 'cfexampleapi');

        $client->getSpace();

        $responseInfo = $client->getLastResponseInfo();

        $this->assertInstanceOf(DeliveryResponseInfo::class, $responseInfo);
        $this->assertRegexp('/([a-z0-9]{32})/', $responseInfo->getRequestId());

        $this->assertContains($responseInfo->getCache(), ['HIT', 'MISS']);
        $this->assertGreaterThanOrEqual(0, $responseInfo->getCacheHits());
    }

    /**
     * @vcr e2e_response_info_preview.json
     */
    public function testPreviewResponseInfo()
    {
        $client = new Client('81c469d7241ca02349388602dfc14107157063a6901c378a56e1835d688970bf', '88dyiqcr7go8', true);

        $client->getSpace();

        $responseInfo = $client->getLastResponseInfo();

        $this->assertInstanceOf(PreviewResponseInfo::class, $responseInfo);
        $this->assertRegexp('/([a-z0-9]{32})/', $responseInfo->getRequestId());
    }
}

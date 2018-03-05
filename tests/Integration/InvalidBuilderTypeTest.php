<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery\Integration;

use Contentful\Delivery\Client;
use Contentful\Tests\Delivery\TestCase;

class InvalidBuilderTypeTest extends TestCase
{
    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Unexpected system type "invalidType" while trying to build a resource.
     */
    public function testExceptionOnInvalidSysType()
    {
        $client = new Client('irrelevant', '7dh3w86is8ls', 'master');

        $client->getResourceBuilder()->build([
            'sys' => [
                'type' => 'invalidType',
                'id' => 'invalidId',
            ],
        ]);
    }
}

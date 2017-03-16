<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Client;
use Contentful\Delivery\Query;

class ErrorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Contentful\Exception\InvalidQueryException
     * @vcr e2e_error_invalid_query.json
     */
    public function testInvalidQuery()
    {
        $client = new Client('b4c0n73n7fu1', 'cfexampleapi');
        $query = (new Query())
            ->where('name', 0);

        $client->getEntries($query);
    }
}

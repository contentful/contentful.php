<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Client;
use Contentful\Delivery\Query;
use Contentful\Exception\RateLimitExceededException;

class ErrorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Contentful\Exception\NotFoundException
     * @vcr e2e_error_resource_not_found.json
     */
    public function testResourceNotFound()
    {
        $client = new Client('b4c0n73n7fu1', 'cfexampleapi');

        $client->getEntry('not-existing');
    }

    /**
     * @expectedException \Contentful\Exception\AccessTokenInvalidException
     * @vcr e2e_error_access_token_invalid.json
     */
    public function testAccessTokenInvalid()
    {
        $client = new Client('e5e8d4c5c122cf28fc1af3ff77d28bef78a3952957f15067bbc29f2f0dde0b50', 'cfexampleapi');
        $client->getEntries();
    }

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

    /**
     * @expectedException \Contentful\Exception\RateLimitExceededException
     * @vcr e2e_error_rate_limit.json
     */
    public function testRateLimitExceeded()
    {
        $client = new Client('8740056d546471e0640d189615470cc12ce2d3188332352ecfb53edac59c4963', 'bc32cj3kyfet', true);
        $query = new Query();

        try {
            for ($i = 1; $i < 25; $i++) {
                $query->setLimit($i);
                $client->getEntries($query);
            }
        } catch (RateLimitExceededException $e) {
            $this->assertInternalType('int', $e->getRateLimitReset());

            throw $e;
        }
    }
}

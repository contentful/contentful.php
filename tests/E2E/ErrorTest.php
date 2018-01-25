<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\E2E;

use Contentful\Delivery\Query;
use Contentful\Exception\RateLimitExceededException;
use Contentful\Tests\Delivery\End2EndTestCase;

class ErrorTest extends End2EndTestCase
{
    /**
     * @expectedException \Contentful\Exception\NotFoundException
     * @vcr e2e_error_resource_not_found.json
     */
    public function testResourceNotFound()
    {
        $client = $this->getClient('cfexampleapi');

        $client->getEntry('not-existing');
    }

    /**
     * @expectedException \Contentful\Exception\AccessTokenInvalidException
     * @vcr e2e_error_access_token_invalid.json
     */
    public function testAccessTokenInvalid()
    {
        $client = $this->getClient('cfexampleapi_invalid');

        $client->getEntries();
    }

    /**
     * @expectedException \Contentful\Exception\InvalidQueryException
     * @vcr e2e_error_invalid_query.json
     */
    public function testInvalidQuery()
    {
        $client = $this->getClient('cfexampleapi');

        $query = (new Query())
            ->where('name', 0);

        $client->getEntries($query);
    }

    /**
     * @requires API no-coverage-proxy
     * @expectedException \Contentful\Exception\RateLimitExceededException
     * @vcr e2e_error_rate_limit.json
     */
    public function testRateLimitExceeded()
    {
        $client = $this->getClient('bc32cj3kyfet_preview');

        $query = new Query();

        try {
            for ($i = 1; $i < 25; ++$i) {
                $query->setLimit($i);
                $client->getEntries($query);
            }
        } catch (RateLimitExceededException $e) {
            $this->assertInternalType('int', $e->getRateLimitReset());

            throw $e;
        }
    }

    /**
     * @expectedException \Contentful\Exception\BadRequestException
     * @vcr e2e_error_bad_request.json
     */
    public function testBadRequest()
    {
        $client = $this->getClient('cfexampleapi');

        $client->getEntry('nyancat', 'invalidLocale');
    }
}

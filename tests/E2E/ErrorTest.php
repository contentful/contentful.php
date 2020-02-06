<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2020 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\E2E;

use Contentful\Core\Exception\RateLimitExceededException;
use Contentful\Delivery\Query;
use Contentful\Tests\Delivery\TestCase;

class ErrorTest extends TestCase
{
    /**
     * @expectedException \Contentful\Core\Exception\NotFoundException
     * @vcr error_resource_not_found.json
     */
    public function testResourceNotFound()
    {
        $client = $this->getClient('default');

        $client->getEntry('not-existing');
    }

    /**
     * @expectedException \Contentful\Core\Exception\AccessTokenInvalidException
     * @vcr error_access_token_invalid.json
     */
    public function testAccessTokenInvalid()
    {
        $client = $this->getClient('default_invalid');

        $client->getEntries();
    }

    /**
     * @expectedException \Contentful\Core\Exception\InvalidQueryException
     * @vcr error_invalid_query.json
     */
    public function testInvalidQuery()
    {
        $client = $this->getClient('default');

        $query = (new Query())
            ->where('name', 0)
        ;

        $client->getEntries($query);
    }

    /**
     * @expectedException \Contentful\Core\Exception\RateLimitExceededException
     * @vcr error_rate_limit_exceeded.json
     */
    public function testRateLimitExceeded()
    {
        $this->skipIfApiCoverage();

        $client = $this->getClient('rate_limit');

        $query = new Query();

        try {
            for ($i = 1; $i < 25; ++$i) {
                $query->setLimit($i);
                $client->getEntries($query);
            }
        } catch (RateLimitExceededException $exception) {
            $this->assertInternalType('int', $exception->getRateLimitReset());

            throw $exception;
        }
    }

    /**
     * @expectedException \Contentful\Core\Exception\BadRequestException
     * @vcr error_bad_request.json
     */
    public function testBadRequest()
    {
        $client = $this->getClient('default');

        $client->getEntry('nyancat', 'invalidLocale');
    }
}

<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\E2E;

use Contentful\Core\Exception\AccessTokenInvalidException;
use Contentful\Core\Exception\BadRequestException;
use Contentful\Core\Exception\InvalidQueryException;
use Contentful\Core\Exception\NotFoundException;
use Contentful\Core\Exception\RateLimitExceededException;
use Contentful\Delivery\Query;
use Contentful\Tests\Delivery\TestCase;

class ErrorTest extends TestCase
{
    /**
     * @vcr error_resource_not_found.json
     */
    public function testResourceNotFound()
    {
        $this->expectException(NotFoundException::class);
        $client = $this->getClient('default');

        $client->getEntry('not-existing');
    }

    /**
     * @vcr error_access_token_invalid.json
     */
    public function testAccessTokenInvalid()
    {
        $this->expectException(AccessTokenInvalidException::class);
        $client = $this->getClient('default_invalid');

        $client->getEntries();
    }

    /**
     * @vcr error_invalid_query.json
     */
    public function testInvalidQuery()
    {
        $this->expectException(InvalidQueryException::class);

        $client = $this->getClient('default');

        $query = (new Query())
            ->where('name', 0)
        ;

        $client->getEntries($query);
    }

    /**
     * @vcr error_rate_limit_exceeded.json
     */
    public function testRateLimitExceeded()
    {
        $this->expectException(RateLimitExceededException::class);

        $this->skipIfApiCoverage();

        $client = $this->getClient('rate_limit');

        $query = new Query();

        try {
            for ($i = 1; $i < 25; ++$i) {
                $query->setLimit($i);
                $client->getEntries($query);
            }
        } catch (RateLimitExceededException $exception) {
            $this->assertIsInt($exception->getRateLimitReset());

            throw $exception;
        }
    }

    /**
     * @vcr error_bad_request.json
     */
    public function testBadRequest()
    {
        $this->expectException(BadRequestException::class);

        $client = $this->getClient('default');

        $client->getEntry('nyancat', 'invalidLocale');
    }
}

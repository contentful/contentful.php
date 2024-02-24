<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Tests\Delivery\Unit;

use Contentful\Delivery\ScopedJsonDecoder;
use Contentful\Tests\Delivery\TestCase;

class ScopedJsonDecoderTest extends TestCase
{
    /**
     * @var ScopedJsonDecoder
     */
    private $scopedJsonDecoder;

    protected function setUp(): void
    {
        $this->scopedJsonDecoder = new ScopedJsonDecoder('cfexampleapi', 'master');

        parent::setUp();
    }

    public function testParseJsonInvalid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->scopedJsonDecoder->decode('{"sys": {"type": "}}');
    }

    public function testParseJsonSpaceMismatch()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Trying to parse and build a JSON structure with a client configured for handling space "cfexampleapi" and environment "master", but space "wrongspace" and environment "master" were detected.');

        $this->scopedJsonDecoder->decode($this->getFixtureContent('space_mismatch.json'));
    }

    public function testParseJsonContentTypeSpaceMismatch()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Trying to parse and build a JSON structure with a client configured for handling space "cfexampleapi" and environment "master", but space "wrongspace" and environment "master" were detected.');

        $this->scopedJsonDecoder->decode($this->getFixtureContent('content_type_space_mismatch.json'));
    }

    public function testParseJsonEmptyObject()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Trying to parse and build a JSON structure with a client configured for handling space "cfexampleapi" and environment "master", but space "[blank]" and environment "master" were detected.');

        $this->scopedJsonDecoder->decode('{}');
    }

    public function testParseJsonInvalidArray()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Trying to parse and build a JSON structure with a client configured for handling space "cfexampleapi" and environment "master", but space "invalidSpace" and environment "invalidEnvironment" were detected.');

        $this->scopedJsonDecoder->decode($this->getFixtureContent('invalid_array.json'));
    }
}

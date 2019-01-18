<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2019 Contentful GmbH
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

    public function setUp()
    {
        $this->scopedJsonDecoder = new ScopedJsonDecoder('cfexampleapi', 'master');

        parent::setUp();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testParseJsonInvalid()
    {
        $this->scopedJsonDecoder->decode('{"sys": {"type": "}}');
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Trying to parse and build a JSON structure with a client configured for handling space "cfexampleapi" and environment "master", but space "wrongspace" and environment "master" were detected.
     */
    public function testParseJsonSpaceMismatch()
    {
        $this->scopedJsonDecoder->decode($this->getFixtureContent('space_mismatch.json'));
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Trying to parse and build a JSON structure with a client configured for handling space "cfexampleapi" and environment "master", but space "wrongspace" and environment "master" were detected.
     */
    public function testParseJsonContentTypeSpaceMismatch()
    {
        $this->scopedJsonDecoder->decode($this->getFixtureContent('content_type_space_mismatch.json'));
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Trying to parse and build a JSON structure with a client configured for handling space "cfexampleapi" and environment "master", but space "[blank]" and environment "master" were detected.
     */
    public function testParseJsonEmptyObject()
    {
        $this->scopedJsonDecoder->decode('{}');
    }

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage Trying to parse and build a JSON structure with a client configured for handling space "cfexampleapi" and environment "master", but space "invalidSpace" and environment "invalidEnvironment" were detected.
     */
    public function testParseJsonInvalidArray()
    {
        $this->scopedJsonDecoder->decode($this->getFixtureContent('invalid_array.json'));
    }
}

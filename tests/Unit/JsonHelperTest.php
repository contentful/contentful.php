<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit;

use Contentful\JsonHelper;

class JsonHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testDecode()
    {
        $data = JsonHelper::decode('{"foo": "bar"}');

        $this->assertInternalType('array', $data);
        $this->assertSame('bar', $data['foo']);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testDecodeInvalid()
    {
        JsonHelper::decode('{"foo": "bar}');
    }

    public function testEncode()
    {
        $json = JsonHelper::encode(['foo' => 'bar']);

        $this->assertJson($json);
        $this->assertJsonStringEqualsJsonString('{"foo": "bar"}', $json);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testEncodeInvalid()
    {
        JsonHelper::encode(["fo\x99o" => 'bar']);
    }
}

<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Delivery\Unit\Synchronization;

use Contentful\Delivery\Synchronization\Result;

class ResultTest extends \PHPUnit_Framework_TestCase
{
    public function testGetter()
    {
        $arr = [];
        $result = new Result($arr, 'token', false);

        $this->assertSame($arr, $result->getItems());
        $this->assertSame('token', $result->getToken());
        $this->assertFalse($result->isDone());
    }
}

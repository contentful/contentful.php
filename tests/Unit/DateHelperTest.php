<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit;

use Contentful\DateHelper;

class DateHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider formatForJsonDataProvider
     */
    public function testFormatForJson($expectedOutput, $dateString)
    {
        $dt = new \DateTimeImmutable($dateString);

        $this->assertSame($expectedOutput, \Contentful\format_date_for_json($dt));
        $this->assertSame($expectedOutput, DateHelper::formatForJson($dt));
    }

    public function formatForJsonDataProvider()
    {
        return [
            'with milliseconds' => ['2014-08-11T08:30:42.559Z', '2014-08-11T08:30:42.559Z'],
            'without milliseconds' => ['2014-08-11T08:30:42Z', '2014-08-11T08:30:42Z'],
            'with milliseconds set to 0' => ['2014-08-11T08:30:42Z', '2014-08-11T08:30:42.000Z'],
        ];
    }
}

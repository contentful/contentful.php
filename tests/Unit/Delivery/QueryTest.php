<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\Delivery;

use Contentful\Delivery\Query;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    public function testQueryStringInclude()
    {
        $query = new Query();
        $query->setInclude(50);

        $this->assertSame('include=50', $query->getQueryString());
    }

    public function testQueryStringLocale()
    {
        $query = new Query();
        $query->setLocale('de-DE');

        $this->assertSame('locale=de-DE', $query->getQueryString());
    }

    public function testIncomingLinks()
    {
        $query = new Query();
        $query->linksToEntry('entryId');

        $this->assertSame('links_to_entry=entryId', $query->getQueryString());

        $query = new Query();
        $query->linksToAsset('assetId');

        $this->assertSame('links_to_asset=assetId', $query->getQueryString());
    }
}

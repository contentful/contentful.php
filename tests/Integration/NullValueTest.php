<?php
/**
 * @copyright 2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Integration;

use Contentful\Delivery\Client;

/**
 * The Contentful API differentiates between `null` and no value. For some fields this is rather rare as the web app
 * never sets the value to null. This test cases should cover these corner cases.
 */
class NullValueTest extends \PHPUnit_Framework_TestCase
{
    public function testNullAsValueForLink()
    {
        $client = new Client('irrelevant', 'rue07lqzt1co');

        // First we load the Space
        $client->reviveJson('{"sys":{"type":"Space","id":"rue07lqzt1co"},"name":"Product Catalog App","locales":[{"code":"en-US","default":true,"name":"U.S. English","fallbackCode":null}]}');

        // then the Content Type
        $client->reviveJson('{"fields":[{"name":"Company name","id":"companyName","type":"Text","required":true},{"name":"Logo","id":"logo","type":"Link","linkType":"Asset"},{"name":"Description","id":"companyDescription","type":"Text"},{"name":"Website","id":"website","type":"Symbol"},{"name":"Twitter","id":"twitter","type":"Symbol"},{"name":"Email","id":"email","type":"Symbol"},{"name":"Phone #","id":"phone","type":"Array","items":{"type":"Symbol"}}],"name":"Brand","displayField":"companyName","sys":{"space":{"sys":{"type":"Link","linkType":"Space","id":"rue07lqzt1co"}},"type":"ContentType","id":"sFzTZbSuM8coEwygeUYes","revision":1,"createdAt":"2015-01-15T16:02:08.382Z","updatedAt":"2015-01-15T16:02:08.382Z"}}');

        // lastly, the entry
        $entry = $client->reviveJson('{"fields":{"companyName":{"en-US":"Lemnos"},"website":{"en-US":"http://www.lemnos.jp/en/"},"email":{"en-US":"info@acgears.com"},"phone":{"en-US":["+1 212 260 2269"]},"logo":{"en-US":null},"companyDescription":{"en-US":"TAKATA Lemnos Inc. was founded in 1947 as a brass casting manufacturing industry in Takaoka-city, Toyama Prefecture, Japan and we launched out into the full-scale business trade with Seiko Clock Co., Ltd. since 1966.\n\nWe entered into the development for the original planning from late 1980 and \"Lemnos Brand\" recognized as the global design clock by a masterpiece \"HOLA\" designed by Kazuo KAWASAKI which released in 1989.\n\nAfterwards, we made a lot of projects with well-known designers who took in active in Japan and overseas such as Riki WATANABE, Kazuo KAWASAKI, Shin AZUMI, Tomoko AZUMI, Kanae TSUKAMOTO etc. and we made announcement of their fine works abounding in artistry and prominent designs. In addition, we realized to make a special project by the collaboration with Andrea Branzi, a well-known architect in the world.\n\nLemnos brand products are now highly praised from the design shops and the interior shops all over the world.\n\nIn recent years, we also have been given high priority to develop interior accessories making full use of our traditional techniques by the founding manufacturer and we always focus our minds on the development for the new Lemnos products in the new market.\n\nOur Lemnos products are made carefully by our craftsmen finely honed skillful techniques in Japan. They surely bring out the attractiveness of the materials to the maximum and create fine products not being influenced on the fashion trend accordingly. TAKATA Lemnos Inc. definitely would like to be innovative and continuously propose the beauty lasts forever."}},"sys":{"space":{"sys":{"type":"Link","linkType":"Space","id":"rue07lqzt1co"}},"type":"Entry","contentType":{"sys":{"type":"Link","linkType":"ContentType","id":"sFzTZbSuM8coEwygeUYes"}},"id":"4LgMotpNF6W20YKmuemW0a","revision":1,"createdAt":"2015-01-15T16:02:27.122Z","updatedAt":"2015-01-15T16:02:27.122Z"}}');

        $this->assertNull($entry->getLogo());
    }
}

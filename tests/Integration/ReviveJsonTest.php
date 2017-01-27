<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Integration;

use Contentful\Delivery\Client;

class ReviveJsonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client
     */
    private $client;

    public function setUp()
    {
        $this->client = new Client('b4c0n73n7fu1', 'cfexampleapi');

        parent::setUp();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testReviveJsonInvalid()
    {
        $this->client->reviveJson('{"sys": {"type": "}}');
    }

    /**
     * @expectedException \Contentful\Delivery\SpaceMismatchException
     */
    public function testReviveJsonSpaceMismatch()
    {
        $json = '{"sys": {"type": "Space","id": "wrongspace"},"name": "Contentful Example API","locales": [{"code": "en-US","default": true,"name": "English", "fallbackCode": null},{"code": "tlh","default": false,"name": "Klingon", "fallbackCode": "en-US"}]}';

        $this->client->reviveJson($json);
    }

    /**
     * @expectedException \Contentful\Delivery\SpaceMismatchException
     */
    public function testReviveJsonContentTypeSpaceMismatch()
    {
        $json = '{"sys": {"space": {"sys": {"type": "Link","linkType": "Space","id": "wrongspace"}},"type": "ContentType","id": "cat","revision": 2,"createdAt": "2013-06-27T22:46:12.852Z","updatedAt": "2013-09-02T13:14:47.863Z"},"fields": [{"id": "name","name": "Name","type": "Text","required": true,"localized": true},{"id": "likes","name": "Likes","type": "Array","required": false,"localized": false,"items": {"type": "Symbol"}},{"id": "color","name": "Color","type": "Symbol","required": false,"localized": false},{"id": "bestFriend","name": "Best Friend","type": "Link","required": false,"localized": false,"linkType": "Entry"},{"id": "birthday","name": "Birthday","type": "Date","required": false,"localized": false},{"id": "lifes","name": "Lifes left","type": "Integer","required": false,"localized": false,"disabled": true},{"id": "lives","name": "Lives left","type": "Integer","required": false,"localized": false},{"id": "image","name": "Image","required": false,"localized": false,"type": "Link","linkType": "Asset"}],"name": "Cat","displayField": "name", "description": "Meow."}';

        $this->client->reviveJson($json);
    }

    public function testReviveJsonSpace()
    {
        $json = '{"sys": {"type": "Space","id": "cfexampleapi"},"name": "Contentful Example API","locales": [{"code": "en-US","default": true,"name": "English","fallbackCode": null},{"code": "tlh","default": false,"name": "Klingon","fallbackCode": "en-US"}]}';

        $obj = $this->client->reviveJson($json);

        $this->assertJsonStringEqualsJsonString($json, json_encode($obj));
    }

    public function reviveJsonDataProvider()
    {
        return [
            ['{"fields": [{"id": "name","name": "Name","type": "Text","required": true,"localized": true},{"id": "likes","name": "Likes","type": "Array","required": false,"localized": false,"items": {"type": "Symbol"}},{"id": "color","name": "Color","type": "Symbol","required": false,"localized": false},{"id": "bestFriend","name": "Best Friend","type": "Link","required": false,"localized": false,"linkType": "Entry"},{"id": "birthday","name": "Birthday","type": "Date","required": false,"localized": false},{"id": "lifes","name": "Lifes left","type": "Integer","required": false,"localized": false,"disabled": true},{"id": "lives","name": "Lives left","type": "Integer","required": false,"localized": false},{"id": "image","name": "Image","required": false,"localized": false,"type": "Link","linkType": "Asset"}],"name": "Cat","displayField": "name","description": "Meow.","sys": {"space": {"sys": {"type": "Link","linkType": "Space","id": "cfexampleapi"}},"type": "ContentType","id": "cat","revision": 2,"createdAt": "2013-06-27T22:46:12.852Z","updatedAt": "2013-09-02T13:14:47.863Z"}}'],
            ['{"sys": {"type": "DeletedEntry","id": "4rPdazIwWkuuKEAQgemSmO","space": {"sys": {"type": "Link","linkType": "Space","id": "cfexampleapi"}},"revision": 1,"createdAt": "2014-08-11T08:30:42.559Z","updatedAt": "2014-08-11T08:30:42.559Z","deletedAt": "2014-08-11T08:30:42.559Z"}}'],
            ['{"sys": {"type": "DeletedAsset","id": "5c6VY0gWg0gwaIeYkUUiqG","space": {"sys": {"type": "Link","linkType": "Space","id": "cfexampleapi"}},"revision": 1,"createdAt": "2013-09-09T16:17:12.600Z","updatedAt": "2013-09-09T16:17:12.600Z","deletedAt": "2013-09-09T16:17:12.600Z"}}'],
            ['{"fields": {"title": {"en-US": "Nyan Cat"},"file": {"en-US": {"fileName": "Nyan_cat_250px_frame.png","contentType": "image/png","details": {"image": {"width": 250,"height": 250},"size": 12273},"url": "//images.contentful.com/cfexampleapi/4gp6taAwW4CmSgumq2ekUm/9da0cd1936871b8d72343e895a00d611/Nyan_cat_250px_frame.png"}}},"sys": {"space": {"sys": {"type": "Link","linkType": "Space","id": "cfexampleapi"}},"type": "Asset","id": "nyancat","revision": 1,"createdAt": "2013-09-02T14:56:34.240Z","updatedAt": "2013-09-02T14:56:34.240Z"}}'],
        ];
    }

    /**
     * @dataProvider reviveJsonDataProvider
     */
    public function testReviveAndEncodeJson($json)
    {
        $space = '{"sys": {"type": "Space","id": "cfexampleapi"},"name": "Contentful Example API","locales": [{"code": "en-US","default": true,"name": "English","fallbackCode": null},{"code": "tlh","default": false,"name": "Klingon","fallbackCode": "en-US"}]}';
        $this->client->reviveJson($space);

        $obj = $this->client->reviveJson($json);

        $this->assertJsonStringEqualsJsonString($json, json_encode($obj));
    }

    public function testReviveJsonEntry()
    {
        $space = '{"sys": {"type": "Space","id": "cfexampleapi"},"name": "Contentful Example API","locales": [{"code": "en-US","default": true,"name": "English","fallbackCode": null},{"code": "tlh","default": false,"name": "Klingon","fallbackCode": "en-US"}]}';
        $ct = '{"fields": [{"id": "name","name": "Name","type": "Text","required": true,"localized": true},{"id": "likes","name": "Likes","type": "Array","required": false,"localized": false,"items": {"type": "Symbol"}},{"id": "color","name": "Color","type": "Symbol","required": false,"localized": false},{"id": "bestFriend","name": "Best Friend","type": "Link","required": false,"localized": false,"linkType": "Entry"},{"id": "birthday","name": "Birthday","type": "Date","required": false,"localized": false},{"id": "lifes","name": "Lifes left","type": "Integer","required": false,"localized": false,"disabled": true},{"id": "lives","name": "Lives left","type": "Integer","required": false,"localized": false},{"id": "image","name": "Image","required": false,"localized": false,"type": "Link","linkType": "Asset"}],"name": "Cat","displayField": "name","description": "Meow.","sys": {"space": {"sys": {"type": "Link","linkType": "Space","id": "cfexampleapi"}},"type": "ContentType","id": "cat","revision": 2,"createdAt": "2013-06-27T22:46:12.852Z","updatedAt": "2013-09-02T13:14:47.863Z"}}';
        $json = '{"fields": {"name": {"en-US": "Nyan Cat","tlh": "Nyan vIghro\'"},"likes": {"en-US": ["rainbows","fish"]},"color": {"en-US": "rainbow"},"bestFriend": {"en-US": {"sys": {"type": "Link","linkType": "Entry","id": "happycat"}}},"birthday": {"en-US": "2011-04-04T22:00:00.000Z"},"lives": {"en-US": 1337},"image": {"en-US": {"sys": {"type": "Link","linkType": "Asset","id": "nyancat"}}}},"sys": {"space": {"sys": {"type": "Link","linkType": "Space","id": "cfexampleapi"}},"type": "Entry","contentType": {"sys": {"type": "Link","linkType": "ContentType","id": "cat"}},"id": "nyancat","revision": 5,"createdAt": "2013-06-27T22:46:19.513Z","updatedAt": "2013-09-04T09:19:39.027Z"}}';

        $this->client->reviveJson($space);
        $this->client->reviveJson($ct);
        $obj = $this->client->reviveJson($json);

        $this->assertJsonStringEqualsJsonString($json, json_encode($obj));
    }

    public function testReviveJsonSingleLocaleEntry()
    {
        $space = '{"sys": {"type": "Space","id": "cfexampleapi"},"name": "Contentful Example API","locales": [{"code": "en-US","default": true,"name": "English","fallbackCode": null},{"code": "tlh","default": false,"name": "Klingon","fallbackCode": "en-US"}]}';
        $ct = '{"fields": [{"id": "name","name": "Name","type": "Text","required": true,"localized": true},{"id": "likes","name": "Likes","type": "Array","required": false,"localized": false,"items": {"type": "Symbol"}},{"id": "color","name": "Color","type": "Symbol","required": false,"localized": false},{"id": "bestFriend","name": "Best Friend","type": "Link","required": false,"localized": false,"linkType": "Entry"},{"id": "birthday","name": "Birthday","type": "Date","required": false,"localized": false},{"id": "lifes","name": "Lifes left","type": "Integer","required": false,"localized": false,"disabled": true},{"id": "lives","name": "Lives left","type": "Integer","required": false,"localized": false},{"id": "image","name": "Image","required": false,"localized": false,"type": "Link","linkType": "Asset"}],"name": "Cat","displayField": "name","description": "Meow.","sys": {"space": {"sys": {"type": "Link","linkType": "Space","id": "cfexampleapi"}},"type": "ContentType","id": "cat","revision": 2,"createdAt": "2013-06-27T22:46:12.852Z","updatedAt": "2013-09-02T13:14:47.863Z"}}';
        $enUsJson = '{"sys":{"space":{"sys":{"type":"Link","linkType":"Space","id":"cfexampleapi"}},"id":"nyancat","type":"Entry","createdAt":"2013-06-27T22:46:19.513Z","updatedAt":"2013-09-04T09:19:39.027Z","revision":5,"contentType":{"sys":{"type":"Link","linkType":"ContentType","id":"cat"}},"locale":"en-US"},"fields":{"name":"Nyan Cat","likes":["rainbows","fish"],"color":"rainbow","bestFriend":{"sys":{"type":"Link","linkType":"Entry","id":"happycat"}},"birthday":"2011-04-04T22:00:00.000Z","lives":1337,"image":{"sys":{"type":"Link","linkType":"Asset","id":"nyancat"}}}}';
        $tlhJson = '{"sys":{"space":{"sys":{"type":"Link","linkType":"Space","id":"cfexampleapi"}},"id":"nyancat","type":"Entry","createdAt":"2013-06-27T22:46:19.513Z","updatedAt":"2013-09-04T09:19:39.027Z","revision":5,"contentType":{"sys":{"type":"Link","linkType":"ContentType","id":"cat"}},"locale":"tlh"},"fields":{"name":"Nyan vIghro\'","likes":["rainbows","fish"],"color":"rainbow","bestFriend":{"sys":{"type":"Link","linkType":"Entry","id":"happycat"}},"birthday":"2011-04-04T22:00:00.000Z","lives":1337,"image":{"sys":{"type":"Link","linkType":"Asset","id":"nyancat"}}}}';

        $this->client->reviveJson($space);
        $this->client->reviveJson($ct);

        $enUsObj = $this->client->reviveJson($enUsJson);
        $this->assertJsonStringEqualsJsonString($enUsJson, json_encode($enUsObj));

        $tlhObj = $this->client->reviveJson($tlhJson);
        $this->assertJsonStringEqualsJsonString($tlhJson, json_encode($tlhObj));
    }

    public function testReviveJsonSingleLocaleAsset()
    {
        $space = '{"sys": {"type": "Space","id": "cfexampleapi"},"name": "Contentful Example API","locales": [{"code": "en-US","default": true,"name": "English","fallbackCode": null},{"code": "tlh","default": false,"name": "Klingon","fallbackCode": "en-US"}]}';
        $this->client->reviveJson($space);

        $enUsJson = '{"sys":{"space":{"sys":{"type":"Link","linkType":"Space","id":"cfexampleapi"}},"id":"nyancat","type":"Asset","createdAt":"2013-09-02T14:56:34.240Z","updatedAt":"2013-09-02T14:56:34.240Z","revision":1,"locale":"en-US"},"fields":{"title":"Nyan Cat","file":{"url":"//images.contentful.com/cfexampleapi/4gp6taAwW4CmSgumq2ekUm/9da0cd1936871b8d72343e895a00d611/Nyan_cat_250px_frame.png","details":{"size":12273,"image":{"width":250,"height":250}},"fileName":"Nyan_cat_250px_frame.png","contentType":"image/png"}}}';

        $enUsObj = $this->client->reviveJson($enUsJson);
        $this->assertJsonStringEqualsJsonString($enUsJson, json_encode($enUsObj));
    }
}

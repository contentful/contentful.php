<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit\Delivery;

use Contentful\Delivery\Client;
use Contentful\Delivery\Synchronization\Manager;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testIsPreview()
    {
        $client = new Client('b4c0n73n7fu1', 'cfexampleapi');

        $this->assertFalse($client->isPreview());
    }

    public function testGetSynchronizationManager()
    {
        $client = new Client('b4c0n73n7fu1', 'cfexampleapi');

        $this->assertInstanceOf(Manager::class, $client->getSynchronizationManager());
    }

    public function testIsPreviewPreview()
    {
        $client = new Client('b4c0n73n7fu1', 'cfexampleapi', true);

        $this->assertTrue($client->isPreview());
    }

    public function testUserAgent()
    {
        $client = new UserAgentClient('b4c0n73n7fu1', 'cfexampleapi');

        $this->assertRegExp(
            '/^sdk contentful.php\/[0-9\.]*(-(dev|beta|alpha|RC))?; platform PHP\/[0-9\.]*; os (Windows|Linux|macOS);$/',
            $client->getContentfulUserAgent()
        );
    }

    public function testUserAgentWithApplication()
    {
        $client = new UserAgentClient('b4c0n73n7fu1', 'cfexampleapi');

        // Just the app name
        $client->setApplication('TestApp');
        $this->assertRegExp(
            '/^app TestApp; sdk contentful.php\/[0-9\.]*(-(dev|beta|alpha|RC))?; platform PHP\/[0-9\.]*; os (Windows|Linux|macOS);$/',
            $client->getContentfulUserAgent()
        );

        // With a version
        $client->setApplication('TestApp', '3.3.7');
        $this->assertRegExp(
            '/^app TestApp\/3.3.7; sdk contentful.php\/[0-9\.]*(-(dev|beta|alpha|RC))?; platform PHP\/[0-9\.]*; os (Windows|Linux|macOS);$/',
            $client->getContentfulUserAgent()
        );

        // Reset the app
        $client->setApplication(null);
        $this->assertRegExp(
            '/^sdk contentful.php\/[0-9\.]*(-(dev|beta|alpha|RC))?; platform PHP\/[0-9\.]*; os (Windows|Linux|macOS);$/',
            $client->getContentfulUserAgent()
        );
    }

    public function testUserAgentWithIntegration()
    {
        $client = new UserAgentClient('b4c0n73n7fu1', 'cfexampleapi');

        // Just the integration name
        $client->setIntegration('SomeIntegration');
        $this->assertRegExp(
            '/^integration SomeIntegration; sdk contentful.php\/[0-9\.]*(-(dev|beta|alpha|RC))?; platform PHP\/[0-9\.]*; os (Windows|Linux|macOS);$/',
            $client->getContentfulUserAgent()
        );

        // With a version
        $client->setIntegration('SomeIntegration', '2.1.3-beta');
        $this->assertRegExp(
            '/^integration SomeIntegration\/2.1.3-beta; sdk contentful.php\/[0-9\.]*(-(dev|beta|alpha|RC))?; platform PHP\/[0-9\.]*; os (Windows|Linux|macOS);$/',
            $client->getContentfulUserAgent()
        );

        // Reset the integration
        $client->setIntegration(null);
        $this->assertRegExp(
            '/^sdk contentful.php\/[0-9\.]*(-(dev|beta|alpha|RC))?; platform PHP\/[0-9\.]*; os (Windows|Linux|macOS);$/',
            $client->getContentfulUserAgent()
        );
    }

    public function testUserAgentWithAppAndIntegration()
    {
        $client = new UserAgentClient('b4c0n73n7fu1', 'cfexampleapi');

        $client
            ->setApplication('TestApp', '3.3.7')
            ->setIntegration('SomeIntegration', '2.1.3-beta');
        $this->assertRegExp(
            '/^app TestApp\/3.3.7; integration SomeIntegration\/2.1.3-beta; sdk contentful.php\/[0-9\.]*(-(dev|beta|alpha|RC))?; platform PHP\/[0-9\.]*; os (Windows|Linux|macOS);$/',
            $client->getContentfulUserAgent()
        );
    }
}

class UserAgentClient extends Client
{
    public function getContentfulUserAgent()
    {
        return parent::getContentfulUserAgent();
    }
}

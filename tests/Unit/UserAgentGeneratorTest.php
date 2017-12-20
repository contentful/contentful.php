<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Tests\Unit;

use Contentful\UserAgentGenerator;

class UserAgentGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testUserAgent()
    {
        $generator = new UserAgentGenerator('contentful.php', '0.6.2-beta');

        $this->assertRegExp(
            '/^sdk contentful.php\/[0-9\.]*(-(dev|beta|alpha|RC))?; platform PHP\/[0-9\.]*; os (Windows|Linux|macOS);$/',
            $generator->getUserAgent()
        );
    }

    public function testUserAgentWithApplication()
    {
        $generator = new UserAgentGenerator('contentful.php', '0.6.2-beta');

        // Just the app name
        $generator->setApplication('TestApp');
        $this->assertRegExp(
            '/^app TestApp; sdk contentful.php\/[0-9\.]*(-(dev|beta|alpha|RC))?; platform PHP\/[0-9\.]*; os (Windows|Linux|macOS);$/',
            $generator->getUserAgent()
        );

        // With a version
        $generator->setApplication('TestApp', '3.3.7');
        $this->assertRegExp(
            '/^app TestApp\/3.3.7; sdk contentful.php\/[0-9\.]*(-(dev|beta|alpha|RC))?; platform PHP\/[0-9\.]*; os (Windows|Linux|macOS);$/',
            $generator->getUserAgent()
        );

        // Reset the app
        $generator->setApplication(null);
        $this->assertRegExp(
            '/^sdk contentful.php\/[0-9\.]*(-(dev|beta|alpha|RC))?; platform PHP\/[0-9\.]*; os (Windows|Linux|macOS);$/',
            $generator->getUserAgent()
        );
    }

    public function testUserAgentWithIntegration()
    {
        $generator = new UserAgentGenerator('contentful.php', '0.6.2-beta');

        // Just the integration name
        $generator->setIntegration('SomeIntegration');
        $this->assertRegExp(
            '/^integration SomeIntegration; sdk contentful.php\/[0-9\.]*(-(dev|beta|alpha|RC))?; platform PHP\/[0-9\.]*; os (Windows|Linux|macOS);$/',
            $generator->getUserAgent()
        );

        // With a version
        $generator->setIntegration('SomeIntegration', '2.1.3-beta');
        $this->assertRegExp(
            '/^integration SomeIntegration\/2.1.3-beta; sdk contentful.php\/[0-9\.]*(-(dev|beta|alpha|RC))?; platform PHP\/[0-9\.]*; os (Windows|Linux|macOS);$/',
            $generator->getUserAgent()
        );

        // Reset the integration
        $generator->setIntegration(null);
        $this->assertRegExp(
            '/^sdk contentful.php\/[0-9\.]*(-(dev|beta|alpha|RC))?; platform PHP\/[0-9\.]*; os (Windows|Linux|macOS);$/',
            $generator->getUserAgent()
        );
    }

    public function testUserAgentWithAppAndIntegration()
    {
        $generator = new UserAgentGenerator('contentful.php', '0.6.2-beta');

        $generator
            ->setApplication('TestApp', '3.3.7')
            ->setIntegration('SomeIntegration', '2.1.3-beta');
        $this->assertRegExp(
            '/^app TestApp\/3.3.7; integration SomeIntegration\/2.1.3-beta; sdk contentful.php\/[0-9\.]*(-(dev|beta|alpha|RC))?; platform PHP\/[0-9\.]*; os (Windows|Linux|macOS);$/',
            $generator->getUserAgent()
        );
    }
}

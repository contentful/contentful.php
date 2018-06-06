<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Console;

use Contentful\Delivery\Client;
use Symfony\Component\Console\Application as AbstractApplication;

/**
 * CLI Application with Helpers for the Contentful SDK.
 */
class Application extends AbstractApplication
{
    public function __construct()
    {
        parent::__construct('contentful', Client::VERSION);
    }

    protected function getDefaultCommands()
    {
        $defaultCommands = parent::getDefaultCommands();
        $defaultCommands[] = new WarmUpCacheCommand();
        $defaultCommands[] = new ClearCacheCommand();

        return $defaultCommands;
    }
}

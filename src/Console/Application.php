<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Console;

use Contentful\Delivery\Console\ClearCacheCommand;
use Contentful\Delivery\Console\WarmUpCacheCommand;
use Symfony\Component\Console\Application as AbstractApplication;
use Contentful\Delivery\Client;

/**
 * CLI Application with Helpers for the Contentful SDK.
 *
 * @internal
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
        $defaultCommands[] = new WarmUpCacheCommand;
        $defaultCommands[] = new ClearCacheCommand;

        return $defaultCommands;
    }
}

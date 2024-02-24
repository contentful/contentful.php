<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Console;

use Symfony\Component\Console\Application as AbstractApplication;

/**
 * CLI Application with Helpers for the Contentful SDK.
 */
class Application extends AbstractApplication
{
    public function __construct()
    {
        parent::__construct('contentful');
    }

    protected function getDefaultCommands(): array
    {
        $defaultCommands = parent::getDefaultCommands();
        $defaultCommands[] = new WarmUpCacheCommand();
        $defaultCommands[] = new ClearCacheCommand();

        return $defaultCommands;
    }
}

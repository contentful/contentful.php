<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Console;

use Contentful\Delivery\Console\Command\GenerateClassesCommand;
use Symfony\Component\Console\Application as AbstractApplication;

/**
 * CLI Application with Helpers for the Contentful SDK.
 *
 * @internal
 */
class Application extends AbstractApplication
{
    public function __construct()
    {
        parent::__construct('contentful', '0.0.1');
    }

    protected function getDefaultCommands()
    {
        $defaultCommands = parent::getDefaultCommands();
        $defaultCommands[] = new GenerateClassesCommand;

        return $defaultCommands;
    }
}

<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Console;

use Contentful\Delivery\Cache\CacheClearer;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class ClearCacheCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('delivery:cache:clear')
            ->setDefinition([
                new InputArgument(
                    'space-id', InputArgument::REQUIRED,
                    'ID of the space to use.'
                ),
                new InputArgument(
                    'cache-dir', InputArgument::REQUIRED,
                    'The directory to write the cache to.'
                ),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $spaceId = $input->getArgument('space-id');
        $cacheDir = $input->getArgument('cache-dir');

        $cache = new FilesystemAdapter($spaceId, 0, $cacheDir);
        $clearer = new CacheClearer($cache);
        $clearer->clear();

        $output->writeln(\sprintf('<info>Cache cleared for the space %s.</info>', $spaceId));
    }
}

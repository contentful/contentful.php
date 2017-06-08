<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Console;

use Contentful\Delivery\Cache\CacheClearer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
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

        $fs = new Filesystem();

        if (!$fs->exists($cacheDir)) {
            throw new \InvalidArgumentException(
                sprintf("Cache directory '%s' does not exist.", $cacheDir)
            );
        }
        if (!is_writable($cacheDir)) {
            throw new \InvalidArgumentException(
                sprintf("Cache directory '%s' can not be written to.", $cacheDir)
            );
        }

        $clearer = new CacheClearer($spaceId);
        $clearer->clear($cacheDir);

        $output->writeln(sprintf('<info>Cache cleared for the space %s.</info>', $spaceId));
    }
}

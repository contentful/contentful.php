<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Console;

use Contentful\Delivery\Cache\CacheWarmer;
use Contentful\Delivery\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Filesystem\Filesystem;

class WarmUpCacheCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('delivery:cache:warmup')
            ->setDefinition([
                new InputArgument(
                    'space-id', InputArgument::REQUIRED,
                    'ID of the space to use.'
                ),
                new InputArgument(
                    'token', InputArgument::REQUIRED,
                    'Token to access the space.'
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
        $token = $input->getArgument('token');
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

        $client = new Client($token, $spaceId);
        $warmer = new CacheWarmer($client);

        $warmer->warmUp($cacheDir);

        $output->writeln(sprintf('<info>Cache warmed for the space %s.</info>', $spaceId));
    }
}

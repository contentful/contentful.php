<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Console;

use Contentful\Delivery\Cache\CacheWarmer;
use Contentful\Delivery\Client;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

        $client = new Client($token, $spaceId);
        $cache = new FilesystemAdapter($spaceId, 0, $cacheDir);
        $warmer = new CacheWarmer($client, $cache);

        $warmer->warmUp();

        $output->writeln(\sprintf('<info>Cache warmed for the space %s.</info>', $spaceId));
    }
}

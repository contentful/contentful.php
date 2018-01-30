<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Console;

use Contentful\Delivery\Cache\CacheItemPoolFactoryInterface;
use Contentful\Delivery\Cache\CacheWarmer;
use Contentful\Delivery\Client;
use Psr\Cache\CacheItemPoolInterface;
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
                    'cache-item-pool-factory-class', InputArgument::REQUIRED,
                    'The FQCN of a class to be used as a cache item pool factory. Must implement \Contentful\Delivery\Cache\CacheItemPoolFactoryInterface.'
                ),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $spaceId = $input->getArgument('space-id');
        $token = $input->getArgument('token');

        $cachePoolFactoryClass = $input->getArgument('cache-item-pool-factory-class');
        $cacheItemPoolFactory = new $cachePoolFactoryClass();
        if (!$cacheItemPoolFactory instanceof CacheItemPoolFactoryInterface) {
            throw new \InvalidArgumentException("Cache item pool factory class must implement \Contentful\Delivery\Cache\CacheItemPoolFactoryInterface");
        }

        $cacheItemPool = $cacheItemPoolFactory->getCacheItemPool($spaceId);
        if (!$cacheItemPool instanceof CacheItemPoolInterface) {
            throw new \InvalidArgumentException('Cache item pool must be a PSR-6 compatible.');
        }

        $client = new Client($token, $spaceId);
        $warmer = new CacheWarmer($client, $cacheItemPool);

        $warmer->warmUp();

        $output->writeln(\sprintf('<info>Cache warmed for the space %s.</info>', $spaceId));
    }
}

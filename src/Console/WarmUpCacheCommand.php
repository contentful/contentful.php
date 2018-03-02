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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WarmUpCacheCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('delivery:cache:warmup')
            ->setDefinition([
                new InputArgument(
                    'space-id',
                    InputArgument::REQUIRED,
                    'ID of the space to use.'
                ),
                new InputArgument(
                    'access-token',
                    InputArgument::REQUIRED,
                    'Token to access the space.'
                ),
                new InputArgument(
                    'cache-item-pool-factory-class',
                    InputArgument::REQUIRED,
                    'The FQCN of a class to be used as a cache item pool factory. Must implement \Contentful\Delivery\Cache\CacheItemPoolFactoryInterface.'
                ),
                new InputOption(
                    'use-preview',
                    null,
                    InputOption::VALUE_NONE,
                    'Whether to use the Preview API instead of the default Delivery API'
                ),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $spaceId = $input->getArgument('space-id');
        $accessToken = $input->getArgument('access-token');
        $usePreview = $input->getOption('use-preview');

        $client = new Client($accessToken, $spaceId, $usePreview);
        $api = $client->getApi();

        $factoryClass = $input->getArgument('cache-item-pool-factory-class');
        $cacheItemPoolFactory = new $factoryClass();
        if (!$cacheItemPoolFactory instanceof CacheItemPoolFactoryInterface) {
            throw new \InvalidArgumentException(\sprintf(
                'Cache item pool factory must implement "%s".',
                CacheItemPoolFactoryInterface::class
            ));
        }

        $cacheItemPool = $cacheItemPoolFactory->getCacheItemPool($api, $spaceId);
        if (!$cacheItemPool instanceof CacheItemPoolInterface) {
            throw new \InvalidArgumentException(\sprintf(
                'Object returned by "%s::getCacheItemPool()" must be PSR-6 compatible and implement "%s".',
                $factoryClass,
                CacheItemPoolInterface::class
            ));
        }

        $warmer = new CacheWarmer($client, $cacheItemPool);

        if (!$warmer->warmUp()) {
            throw new \RuntimeException(\sprintf(
                'The SDK could not warm up the cache. Try checking your PSR-6 implementation (class "%s").',
                \get_class($cacheItemPool)
            ));
        }

        $output->writeln(\sprintf('<info>Cache warmed for the space "%s" using API "%s".</info>', $spaceId, $api));
    }
}

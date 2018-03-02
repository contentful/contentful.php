<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Console;

use Contentful\Delivery\Cache\CacheClearer;
use Contentful\Delivery\Cache\CacheItemPoolFactoryInterface;
use Contentful\Delivery\Client;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCacheCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('delivery:cache:clear')
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
                    \sprintf(
                        'The FQCN of a factory class which implements "%s".',
                        CacheItemPoolFactoryInterface::class
                    )
                ),
                new InputOption(
                    'use-preview',
                    null,
                    InputOption::VALUE_NONE
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

        $clearer = new CacheClearer($client, $cacheItemPool);

        if (!$clearer->clear()) {
            throw new \RuntimeException(\sprintf(
                'The SDK could not clear the cache. Try checking your PSR-6 implementation (class "%s").',
                \get_class($cacheItemPool)
            ));
        }

        $output->writeln(\sprintf('Cache cleared for space "%s" using API "%s".', $spaceId, $api));
    }
}

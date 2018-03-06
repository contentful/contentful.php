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
                new InputOption('access-token', 't', InputOption::VALUE_REQUIRED, 'Token to access the space.'),
                new InputOption('space-id', 's', InputOption::VALUE_REQUIRED, 'ID of the space to use.'),
                new InputOption('environment-id', 'e', InputOption::VALUE_REQUIRED, 'ID of the environment to use', 'master'),
                new InputOption('factory-class', 'f', InputOption::VALUE_REQUIRED, \sprintf(
                    'The FQCN of a factory class which implements "%s".',
                    CacheItemPoolFactoryInterface::class
                )),
                new InputOption('use-preview', 'p', InputOption::VALUE_NONE),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $accessToken = $input->getOption('access-token');
        $spaceId = $input->getOption('space-id');
        $environmentId = $input->getOption('environment-id');
        $usePreview = $input->getOption('use-preview');

        $client = new Client($accessToken, $spaceId, $environmentId, $usePreview);
        $api = $client->getApi();

        $factoryClass = $input->getOption('factory-class');
        $cacheItemPoolFactory = new $factoryClass();
        if (!$cacheItemPoolFactory instanceof CacheItemPoolFactoryInterface) {
            throw new \InvalidArgumentException(\sprintf(
                'Cache item pool factory must implement "%s".',
                CacheItemPoolFactoryInterface::class
            ));
        }

        $cacheItemPool = $cacheItemPoolFactory->getCacheItemPool($api, $spaceId, $environmentId);
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

        $output->writeln(\sprintf(
            '<info>Cache warmed up for space "%s" on environment "%s" using API "%s".</info>',
            $spaceId,
            $environmentId,
            $api
        ));
    }
}

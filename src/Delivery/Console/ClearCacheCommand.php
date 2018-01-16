<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Console;

use Contentful\Console\CacheItemPoolFactoryInterface;
use Contentful\Delivery\Cache\CacheClearer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCacheCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('delivery:cache:clear')
            ->setDefinition([
                new InputArgument(
                    'cache-item-pool-factory-class', InputArgument::REQUIRED,
                    'The FQCN of a class to be used as a cache item pool factory. Must implement \Contentful\Console\CacheItemPoolFactoryInterface.'
                ),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cachePoolFactoryClass = $input->getArgument('cache-item-pool-factory-class');
        $cacheItemPoolFactory = new $cachePoolFactoryClass();
        if (!$cacheItemPoolFactory instanceof CacheItemPoolFactoryInterface) {
            throw new \InvalidArgumentException("Cache item pool factory class must implement \Contentful\Console\CacheItemPoolFactoryInterface");
        }

        $clearer = new CacheClearer($cacheItemPoolFactory->getCacheItemPool());
        $clearer->clear();

        $output->writeln('<info>Cache cleared.</info>');
    }
}

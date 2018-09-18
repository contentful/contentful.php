<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Console;

use Contentful\Delivery\Cache\CacheWarmer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WarmUpCacheCommand extends BaseCacheCommand
{
    /**
     * {@inheritdoc}
     */
    protected function getCommandName(): string
    {
        return 'delivery:cache:warmup';
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->getClient($input);
        $cacheItemPool = $this->getCacheItemPool($input, $client);
        $cacheContent = (bool) $input->getOption('cache-content');

        $warmer = new CacheWarmer($client, $cacheItemPool);
        if (!$warmer->warmUp($cacheContent)) {
            throw new \RuntimeException(\sprintf(
                'The SDK could not warm up the cache. Try checking your PSR-6 implementation (class "%s").',
                \get_class($cacheItemPool)
            ));
        }

        $output->writeln(\sprintf(
            '<info>Cache warmed up for space "%s" on environment "%s" using API "%s".</info>',
            $client->getSpaceId(),
            $client->getEnvironmentId(),
            $client->getApi()
        ));
    }
}

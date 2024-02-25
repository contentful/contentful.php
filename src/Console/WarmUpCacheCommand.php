<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Console;

use Contentful\Delivery\Cache\CacheWarmer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WarmUpCacheCommand extends BaseCacheCommand
{
    protected function getCommandName(): string
    {
        return 'delivery:cache:warmup';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initClient($input);
        $cacheContent = (bool) $input->getOption('cache-content');

        $warmer = new CacheWarmer($this->client, $this->resourcePool, $this->cacheItemPool);
        if (!$warmer->warmUp($cacheContent)) {
            throw new \RuntimeException(sprintf('The SDK could not warm up the cache. Try checking your PSR-6 implementation (class "%s").', \get_class($this->cacheItemPool)));
        }

        $output->writeln(sprintf(
            '<info>Cache warmed up for space "%s" on environment "%s" using API "%s".</info>',
            $this->client->getSpaceId(),
            $this->client->getEnvironmentId(),
            $this->client->getApi()
        ));

        return 0;
    }
}

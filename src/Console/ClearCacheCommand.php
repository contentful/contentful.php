<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Console;

use Contentful\Delivery\Cache\CacheClearer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCacheCommand extends BaseCacheCommand
{
    protected function getCommandName(): string
    {
        return 'delivery:cache:clear';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initClient($input);
        $cacheContent = (bool) $input->getOption('cache-content');

        $warmer = new CacheClearer($this->client, $this->resourcePool, $this->cacheItemPool);
        if (!$warmer->clear($cacheContent)) {
            throw new \RuntimeException(sprintf('The SDK could not clear the cache. Try checking your PSR-6 implementation (class "%s").', \get_class($this->cacheItemPool)));
        }

        $output->writeln(sprintf(
            '<info>Cache cleared for space "%s" on environment "%s" using API "%s".</info>',
            $this->client->getSpaceId(),
            $this->client->getEnvironmentId(),
            $this->client->getApi()
        ));

        return 0;
    }
}

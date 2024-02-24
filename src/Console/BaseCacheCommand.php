<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Console;

use Contentful\Core\Resource\ResourcePoolInterface;
use Contentful\Delivery\Cache\CacheItemPoolFactoryInterface;
use Contentful\Delivery\Client;
use Contentful\Delivery\Client\ClientInterface;
use Contentful\Delivery\ClientOptions;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

abstract class BaseCacheCommand extends Command
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var ResourcePoolInterface
     */
    protected $resourcePool;

    /**
     * @var CacheItemPoolInterface
     */
    protected $cacheItemPool;

    abstract protected function getCommandName(): string;

    protected function configure()
    {
        $this
            ->setName($this->getCommandName())
            ->setDefinition([
                new InputOption('access-token', 't', InputOption::VALUE_REQUIRED, 'Token to access the space.'),
                new InputOption('space-id', 's', InputOption::VALUE_REQUIRED, 'ID of the space to use.'),
                new InputOption('environment-id', 'e', InputOption::VALUE_REQUIRED, 'ID of the environment to use', 'master'),
                new InputOption('factory-class', 'f', InputOption::VALUE_REQUIRED, sprintf(
                    'The FQCN of a factory class which implements "%s".',
                    CacheItemPoolFactoryInterface::class
                )),
                new InputOption('use-preview', 'p', InputOption::VALUE_NONE, 'Use the Preview API instead of the Delivery API'),
                new InputOption('cache-content', 'c', InputOption::VALUE_NONE, 'Include entries and assets'),
            ])
        ;
    }

    protected function initClient(InputInterface $input)
    {
        /** @var string $accessToken */
        $accessToken = $input->getOption('access-token');
        /** @var string $spaceId */
        $spaceId = $input->getOption('space-id');
        /** @var string $environmentId */
        $environmentId = $input->getOption('environment-id');
        $options = new ClientOptions();
        if ($input->getOption('use-preview')) {
            $options = $options->usingPreviewApi();
        }

        $client = new Client($accessToken, $spaceId, $environmentId, $options);

        $this->client = $client;
        $this->resourcePool = $client->getResourcePool();
        $this->cacheItemPool = $this->getCacheItemPool($input, $client);
    }

    private function getCacheItemPool(InputInterface $input, ClientInterface $client): CacheItemPoolInterface
    {
        $factoryClass = $input->getOption('factory-class');
        $cacheItemPoolFactory = new $factoryClass();
        if (!$cacheItemPoolFactory instanceof CacheItemPoolFactoryInterface) {
            throw new \InvalidArgumentException(sprintf('Cache item pool factory must implement "%s".', CacheItemPoolFactoryInterface::class));
        }

        return $cacheItemPoolFactory->getCacheItemPool(
            $client->getApi(),
            $client->getSpaceId(),
            $client->getEnvironmentId()
        );
    }
}

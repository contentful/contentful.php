<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery;

use Contentful\Core\Resource\ResourceInterface;
use Contentful\Core\Resource\ResourcePoolInterface;
use Contentful\Core\ResourceBuilder\BaseResourceBuilder;
use Contentful\Core\ResourceBuilder\MapperInterface;
use Contentful\Delivery\Client\ClientInterface;
use Contentful\RichText\ParserInterface;

/**
 * ResourceBuilder class.
 *
 * This class is responsible for turning responses from the API into instances of PHP classes.
 *
 * A ResourceBuilder will only work for one space,
 * when working with multiple spaces multiple instances have to be used.
 */
class ResourceBuilder extends BaseResourceBuilder
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var ResourcePoolInterface
     */
    private $resourcePool;

    /**
     * @var ParserInterface
     */
    private $richTextParser;

    /**
     * @var string[]
     */
    private static $availableTypes = [
        'Asset',
        'ContentType',
        'DeletedAsset',
        'DeletedContentType',
        'DeletedEntry',
        'Environment',
        'Entry',
        'Locale',
        'Space',
        'Tag',
    ];

    /**
     * ResourceBuilder constructor.
     */
    public function __construct(
        ClientInterface $client,
        ResourcePoolInterface $resourcePool,
        ParserInterface $richTextParser
    ) {
        $this->client = $client;
        $this->resourcePool = $resourcePool;
        $this->richTextParser = $richTextParser;

        parent::__construct();
    }

    protected function getMapperNamespace(): string
    {
        return __NAMESPACE__.'\\Mapper';
    }

    protected function createMapper($fqcn): MapperInterface
    {
        return new $fqcn($this, $this->client, $this->richTextParser);
    }

    protected function getSystemType(array $data): string
    {
        if ('Array' === $data['sys']['type']) {
            return 'ResourceArray';
        }

        if (\in_array($data['sys']['type'], self::$availableTypes, true)) {
            return $data['sys']['type'];
        }

        throw new \InvalidArgumentException(sprintf('Unexpected system type "%s" while trying to build a resource.', $data['sys']['type']));
    }

    public function build(array $data, ?ResourceInterface $resource = null)
    {
        $type = $data['sys']['type'];

        if ('Array' === $type) {
            $this->buildContentTypeCollection($data);
            $this->buildIncludes($data);

            return parent::build($data);
        }

        $resourceId = $data['sys']['id'];

        // Assets and entries are stored in cache using their locales.
        $locale = null;
        if (\in_array($data['sys']['type'], ['Asset', 'Entry'], true)) {
            $locale = $data['sys']['locale'] ?? '*';
        }

        if ($this->resourcePool->has($type, $resourceId, ['locale' => $locale])) {
            $resource = $this->resourcePool->get($type, $resourceId, ['locale' => $locale]);

            // If it's an entry, we still proceed with the resource building,
            // as it might have fields that were not previously loaded
            // due to the use of the select query operator.
            // For any other resource, we skip the building because
            // we have the result cached already.
            if ('Entry' !== $data['sys']['type']) {
                return $resource;
            }
        }

        $resource = parent::build($data, $resource);

        if ($resource instanceof ResourceInterface) {
            $this->resourcePool->save($resource);
        }

        return $resource;
    }

    /**
     * We extract content types that need to be fetched from a response array.
     * This way we can use a collective query rather than making separate queries
     * for every content type.
     */
    private function buildContentTypeCollection(array $data)
    {
        $items = array_merge(
            $data['items'],
            $data['includes']['Entry'] ?? []
        );

        $ids = array_map(function (array $item) {
            return 'Entry' === $item['sys']['type']
                ? $item['sys']['contentType']['sys']['id']
                : null;
        }, $items);

        $ids = array_filter(array_unique($ids), function ($id): bool {
            return $id && !$this->resourcePool->has('ContentType', $id);
        });

        if ($ids) {
            $query = (new Query())
                ->where('sys.id[in]', implode(',', $ids))
            ;
            $this->client->getContentTypes($query);
        }
    }

    private function buildIncludes(array $data)
    {
        $items = array_merge(
            $data['includes']['Entry'] ?? [],
            $data['includes']['Asset'] ?? []
        );
        foreach ($items as $item) {
            $this->build($item);
        }
    }
}

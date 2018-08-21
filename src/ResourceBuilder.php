<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

use Contentful\Core\Resource\ResourceInterface;
use Contentful\Core\ResourceBuilder\BaseResourceBuilder;

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
     * @var Client
     */
    private $client;

    /**
     * @var InstanceRepository
     */
    private $instanceRepository;

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
    ];

    /**
     * ResourceBuilder constructor.
     *
     * @param Client             $client
     * @param InstanceRepository $instanceRepository
     */
    public function __construct(Client $client, InstanceRepository $instanceRepository)
    {
        $this->client = $client;
        $this->instanceRepository = $instanceRepository;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function getMapperNamespace()
    {
        return __NAMESPACE__.'\\Mapper';
    }

    /**
     * {@inheritdoc}
     */
    protected function createMapper($fqcn)
    {
        return new $fqcn($this, $this->client);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSystemType(array $data)
    {
        if ('Array' === $data['sys']['type']) {
            return 'ResourceArray';
        }

        if (\in_array($data['sys']['type'], self::$availableTypes, true)) {
            return $data['sys']['type'];
        }

        throw new \InvalidArgumentException(\sprintf(
            'Unexpected system type "%s" while trying to build a resource.',
            $data['sys']['type']
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $data, ResourceInterface $resource = null)
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
            $locale = isset($data['sys']['locale']) ? $data['sys']['locale'] : '*';
        }

        if ($this->instanceRepository->has($type, $resourceId, $locale)) {
            $resource = $this->instanceRepository->get($type, $resourceId, $locale);

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
            $this->instanceRepository->set($resource);
        }

        return $resource;
    }

    /**
     * We extract content types that need to be fetched from a response array.
     * This way we can use a collective query rather than making separate queries
     * for every content type.
     *
     * @param array $data
     */
    private function buildContentTypeCollection(array $data)
    {
        $items = \array_merge(
            $data['items'],
            isset($data['includes']['Entry']) ? $data['includes']['Entry'] : []
        );

        $ids = \array_map(function ($item) {
            return 'Entry' === $item['sys']['type']
                ? $item['sys']['contentType']['sys']['id']
                : null;
        }, $items);

        $ids = \array_filter(\array_unique($ids), function ($id) {
            return $id && !$this->instanceRepository->has('ContentType', $id);
        });

        if ($ids) {
            $query = (new Query())
                ->where('sys.id', \implode(',', $ids), 'in');
            $this->client->getContentTypes($query);
        }
    }

    /**
     * @param array $data
     */
    private function buildIncludes(array $data)
    {
        $items = \array_merge(
            isset($data['includes']['Entry']) ? $data['includes']['Entry'] : [],
            isset($data['includes']['Asset']) ? $data['includes']['Asset'] : []
        );
        foreach ($items as $item) {
            $this->build($item);
        }
    }
}

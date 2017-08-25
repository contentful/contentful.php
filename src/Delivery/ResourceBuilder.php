<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

use Contentful\Delivery\Client as DeliveryClient;
use Contentful\Delivery\Synchronization\DeletedAsset;
use Contentful\Delivery\Synchronization\DeletedContentType;
use Contentful\Delivery\Synchronization\DeletedEntry;
use Contentful\Delivery\Cache\InstanceCache;
use Contentful\Delivery\Cache\CacheInterface;
use Contentful\Location;
use Contentful\ResourceArray;
use Contentful\Link;
use Contentful\File\File;
use Contentful\File\FileInterface;
use Contentful\File\ImageFile;
use Contentful\File\UploadFile;
use Contentful\Exception\SpaceMismatchException;
use Contentful\File\LocalUploadFile;

/**
 * The ResourceBuilder is responsible for turning the responses from the API into instances of PHP classes.
 *
 * A ResourceBuilder will only work for one space, when working with multiple spaces multiple ResourceBuilders have to be used.
 */
class ResourceBuilder
{
    /**
     * @var DeliveryClient
     */
    private $client;

    /**
     * @var InstanceCache
     */
    private $instanceCache;

    /**
     * @var CacheInterface
     */
    private $filesystemCache;

    /**
     * The ID of the space this ResourceBuilder is responsible for.
     *
     * @var string
     */
    private $spaceId;

    /**
     * ResourceBuilder constructor.
     *
     * @param Client         $client
     * @param InstanceCache  $instanceCache
     * @param CacheInterface $filesystemCache
     * @param string         $spaceId
     */
    public function __construct(DeliveryClient $client, InstanceCache $instanceCache, CacheInterface $filesystemCache, $spaceId)
    {
        $this->client = $client;
        $this->instanceCache = $instanceCache;
        $this->filesystemCache = $filesystemCache;
        $this->spaceId = $spaceId;
    }

    /**
     * Build objects based on PHP classes from the raw JSON based objects.
     *
     * @param  array $data
     *
     * @return Asset|ContentType|DynamicEntry|Space|DeletedAsset|DeletedContentType|DeletedEntry|ResourceArray
     */
    public function buildObjectsFromRawData(array $data)
    {
        return $this->doBuildObjectsFromRawData($data);
    }

    /**
     * Build objects based on PHP classes from the raw JSON based objects.
     *
     * @param  array       $data
     * @param  array|null  $rawDataList
     *
     * @return Asset|ContentType|DynamicEntry|Space|DeletedAsset|DeletedContentType|DeletedEntry|ResourceArray
     */
    private function doBuildObjectsFromRawData(array $data, array $rawDataList = null)
    {
        $type = $data['sys']['type'];

        switch ($type) {
            case 'Array':
                $itemList = $this->buildArrayDataList($data);

                return $this->buildArray($data, $itemList);
            case 'Asset':
                $id = $data['sys']['id'];
                if (isset($rawDataList['asset'][$id]) && $rawDataList['asset'][$id] instanceof Asset) {
                    return $rawDataList['asset'][$id];
                }

                return $this->buildAsset($data);
            case 'ContentType':
                return $this->buildContentType($data);
            case 'Entry':
                $id = $data['sys']['id'];
                if (isset($rawDataList['entry'][$id]) && $rawDataList['entry'][$id] instanceof DynamicEntry) {
                    return $rawDataList['entry'][$id];
                }

                return $this->buildEntry($data, $rawDataList);
            case 'Space':
                return $this->buildSpace($data);
            case 'DeletedAsset':
                return $this->buildDeletedAsset($data);
            case 'DeletedContentType':
                return $this->buildDeletedContentType($data);
            case 'DeletedEntry':
                return $this->buildDeletedEntry($data);
            default:
                throw new \InvalidArgumentException('Unexpected type "' . $type . '" while trying to build object.');
        }
    }

    /**
     * Builds two hash maps of all the entries and assets that are in a response. These are later used to create
     * the correct object graph.
     *
     * @param  array $data
     *
     * @return array
     */
    private function buildArrayDataList(array $data)
    {
        $entries = [];
        $assets = [];
        $rawEntries = [];

        if (isset($data['includes']['Entry'])) {
            foreach ($data['includes']['Entry'] as $item) {
                $rawEntries[$item['sys']['id']] = $item;
                $entries[$item['sys']['id']] = $this->buildEntry($item);
            }
        }

        if (isset($data['includes']['Asset'])) {
            foreach ($data['includes']['Asset'] as $item) {
                $assets[$item['sys']['id']] = $this->buildAsset($item);
            }
        }

        foreach ($data['items'] as $item) {
            switch ($item['sys']['type']) {
                case 'Asset':
                    $assets[$item['sys']['id']] = $this->buildAsset($item);
                    break;
                case 'Entry':
                    $rawEntries[$item['sys']['id']] = $item;
                    $entries[$item['sys']['id']] = $this->buildEntry($item);
                    break;
                default:
                    // We ignore everything else since it's either cached elsewhere or won't need to be linked
            }
        }

        $dataList = ['asset' => $assets, 'entry' => $entries];

        foreach ($entries as $id => $entry) {
            $this->updateEntry($entry, $rawEntries[$id], $dataList);
        }

        return $dataList;
    }

    /**
     * Build a ResourceArray.
     *
     * @param  array      $data
     * @param  array|null $rawDataList
     *
     * @return ResourceArray
     */
    private function buildArray(array $data, array $rawDataList = null)
    {
        $items = [];
        foreach ($data['items'] as $item) {
            $items[] = $this->doBuildObjectsFromRawData($item, $rawDataList);
        }

        return new ResourceArray($items, $data['total'], $data['limit'], $data['skip']);
    }

    /**
     * Build an Asset.
     *
     * @param  array $data
     *
     * @return Asset
     */
    private function buildAsset(array $data)
    {
        $sys = $this->buildSystemProperties($data['sys']);
        $locale = $sys->getLocale();

        $fields = $data['fields'];
        $files = isset($fields['file']) ? array_map([$this, 'buildFile'], $this->normalizeFieldData($fields['file'], $locale)) : null;

        $asset = new Asset(
            isset($fields['title']) ? $this->normalizeFieldData($fields['title'], $locale) : null,
            isset($fields['description']) ? $this->normalizeFieldData($fields['description'], $locale) : null,
            $files,
            $sys
        );
        if ($locale) {
            $asset->setLocale($locale);
        }

        return $asset;
    }

    /**
     * Creates a File or a subclass thereof.
     *
     * @param  array $data
     *
     * @return FileInterface
     */
    private function buildFile(array $data)
    {
        if (isset($data['uploadFrom'])) {
            // We bypass the buildLink method and instantiate a Link directly because
            // the Upload system type is not actually present in the CDA/CPA.
            return new LocalUploadFile(
                $data['fileName'],
                $data['contentType'],
                new Link($data['uploadFrom']['sys']['id'], $data['uploadFrom']['sys']['linkType'])
            );
        }

        if (isset($data['upload'])) {
            return new UploadFile($data['fileName'], $data['contentType'], $data['upload']);
        }

        $details = $data['details'];
        if (isset($details['image'])) {
            return new ImageFile(
                $data['fileName'],
                $data['contentType'],
                $data['url'],
                $details['size'],
                $details['image']['width'],
                $details['image']['height']
            );
        }

        return new File($data['fileName'], $data['contentType'], $data['url'], $details['size']);
    }

    /**
     * Creates a ContentType.
     *
     * @param  array $data
     *
     * @return ContentType|null
     */
    private function buildContentType(array $data)
    {
        if ($this->instanceCache->hasContentType($data['sys']['id'])) {
            return $this->instanceCache->getContentType($data['sys']['id']);
        }

        $cache = $this->filesystemCache->readContentType($data['sys']['id']);
        if ($cache !== null) {
            $data = \GuzzleHttp\json_decode($cache, true);
        }

        $sys = $this->buildSystemProperties($data['sys']);
        $fields = array_map([$this, 'buildContentTypeField'], $data['fields']);
        $displayField = isset($data['displayField']) ? $data['displayField'] : null;
        $contentType = new ContentType(
            $data['name'],
            isset($data['description']) ? $data['description'] : null,
            $fields,
            $displayField,
            $sys
        );
        $this->instanceCache->addContentType($contentType);

        return $contentType;
    }

    /**
     * Creates a DynamicEntry or a subclass thereof.
     *
     * @param  array      $data
     * @param  array|null $rawDataList
     *
     * @return DynamicEntry
     */
    private function buildEntry(array $data, array $rawDataList = null)
    {
        $sys = $this->buildSystemProperties($data['sys']);
        $locale = $sys->getLocale();
        $fields = [];
        if (isset($data['fields'])) {
            $fields = $this->buildFields($sys->getContentType(), $data['fields'], $locale, $rawDataList);
        }

        $entry = new DynamicEntry(
            $fields,
            $sys,
            $this->client
        );
        if ($locale) {
            $entry->setLocale($locale);
        }

        return $entry;
    }

    private function updateEntry(DynamicEntry $entry, array $data, array $dataList)
    {
        $sys = $this->buildSystemProperties($data['sys']);
        $locale = $sys->getLocale();
        $fields = [];
        if (isset($data['fields'])) {
            $fields = $this->buildFields($sys->getContentType(), $data['fields'], $locale, $dataList);
        }

        // bad rouven, don't use hacks like this
        $entry->__construct($fields,
            $sys,
            $this->client);
        if ($locale) {
            $entry->setLocale($locale);
        }
    }

    /**
     * @param mixed $fieldData
     * @param string|null $locale
     *
     * @return array
     */
    private function normalizeFieldData($fieldData, $locale)
    {
        if (!$locale) {
            return $fieldData;
        }

        return [$locale => $fieldData];
    }

    /**
     * @param ContentType $contentType
     * @param array       $fields
     * @param string|null $locale
     * @param array|null  $rawDataList
     *
     * @return array
     */
    private function buildFields(ContentType $contentType, array $fields, $locale, array $rawDataList = null)
    {
        $result = [];
        foreach ($fields as $name => $fieldData) {
            $result[$name] = $this->buildField($contentType->getField($name), $this->normalizeFieldData($fieldData, $locale), $rawDataList);
        }

        return $result;
    }

    /**
     * @param ContentTypeField $fieldConfig
     * @param array            $fieldData
     * @param array|null       $rawDataList
     *
     * @return array
     */
    private function buildField(ContentTypeField $fieldConfig, array $fieldData, array $rawDataList = null)
    {
        $result = [];
        foreach ($fieldData as $locale => $value) {
            $result[$locale] = $this->formatValue($fieldConfig, $value, $rawDataList);
        }

        return $result;
    }

    /**
     * Transforms values from the original JSON representation to an appropriate PHP representation.
     *
     * @param  ContentTypeField|string $fieldConfig Must be a ContentTypeField if the type is Array
     * @param  mixed                   $value
     * @param  array|null              $rawDataList
     *
     * @return array|Asset|DynamicEntry|Link|Location|\DateTimeImmutable
     */
    private function formatValue($fieldConfig, $value, array $rawDataList = null)
    {
        if ($fieldConfig instanceof ContentTypeField) {
            $type = $fieldConfig->getType();
        } else {
            $type = $fieldConfig;
        }

        if ($value === null) {
            return null;
        }

        switch ($type) {
            case 'Symbol':
            case 'Text':
            case 'Integer':
            case 'Number':
            case 'Boolean':
            case 'Object':
                return $value;
            case 'Date':
                return new \DateTimeImmutable($value, new \DateTimeZone('UTC'));
            case 'Location':
                return new Location($value['lat'], $value['lon']);
            case 'Link':
                return $this->buildLink($value, $rawDataList);
            case 'Array':
                return array_map(function ($value) use ($fieldConfig, $rawDataList) {
                    return $this->formatValue($fieldConfig->getItemsType(), $value, $rawDataList);
                }, $value);
            default:
                throw new \InvalidArgumentException('Unexpected field type "' . $type . '" encountered while trying to format value.');
        }
    }

    /**
     * When an instance of the target already exists, it is returned. If not, a Link is created as placeholder.
     *
     * @param  array      $data
     * @param  array|null $rawDataList
     *
     * @return Asset|DynamicEntry|Link
     *
     * @throws \InvalidArgumentException When encountering an unexpected link type. Only links to assets and entries are currently handled.
     */
    private function buildLink(array $data, array $rawDataList = null)
    {
        $id = $data['sys']['id'];
        $type = $data['sys']['linkType'];

        if ($type === 'Asset') {
            if (isset($rawDataList['asset'][$id])) {
                return $rawDataList['asset'][$id];
            }

            return new Link($id, $type);
        }
        if ($type === 'Entry') {
            if (isset($rawDataList['entry'][$id])) {
                if ($rawDataList['entry'][$id] instanceof DynamicEntry) {
                    return $rawDataList['entry'][$id];
                }
            }

            return new Link($id, $type);
        }

        throw new \InvalidArgumentException(
            'Encountered unexpected resource type "' . $type . '"" while constructing link.'
        );
    }

    /**
     * Retrieves the Space from the API.
     *
     * @param  string $spaceId
     *
     * @return Space
     *
     * @throws SpaceMismatchException When attempting to get a different Space than the one this ResourceBuilder is configured to handle.
     */
    private function getSpace($spaceId)
    {
        if ($spaceId !== $this->spaceId) {
            throw new SpaceMismatchException('This ResourceBuilder is responsible for the space "' . $this->spaceId . '" but was asked to build a resource for the space "' . $spaceId . '".');
        }

        return $this->client->getSpace();
    }

    /**
     * @param  array $data
     *
     * @return Space
     *
     * @throws SpaceMismatchException When attempting to build a different Space than the one this ResourceBuilder is configured to handle.
     */
    private function buildSpace(array $data)
    {
        if ($data['sys']['id'] !== $this->spaceId) {
            throw new SpaceMismatchException('This ResourceBuilder is responsible for the space "' . $this->spaceId . '" but was asked to build a resource for the space "' . $data['sys']['id'] . '".');
        }

        if ($this->instanceCache->hasSpace()) {
            return $this->instanceCache->getSpace();
        }

        $locales = [];
        foreach ($data['locales'] as $locale) {
            $locales[] = new Locale($locale['code'], $locale['name'], $locale['fallbackCode'], $locale['default']);
        }
        $sys = $this->buildSystemProperties($data['sys']);
        $space = new Space($data['name'], $locales, $sys);
        $this->instanceCache->setSpace($space);

        return $space;
    }

    /**
     * @param  array $sys
     *
     * @return SystemProperties
     */
    private function buildSystemProperties(array $sys)
    {
        return new SystemProperties(
            isset($sys['id']) ? $sys['id'] : null,
            isset($sys['type']) ? $sys['type'] : null,
            isset($sys['space']) ? $this->getSpace($sys['space']['sys']['id']) : null,
            isset($sys['contentType']) ? $this->client->getContentType($sys['contentType']['sys']['id']) : null,
            isset($sys['revision']) ? $sys['revision'] : null,
            isset($sys['createdAt']) ? new \DateTimeImmutable($sys['createdAt']) : null,
            isset($sys['updatedAt']) ? new \DateTimeImmutable($sys['updatedAt']) : null,
            isset($sys['deletedAt']) ? new \DateTimeImmutable($sys['deletedAt']) : null,
            isset($sys['locale']) ? $sys['locale'] : null
        );
    }

    /**
     * @param  array $data
     *
     * @return DeletedAsset
     */
    private function buildDeletedAsset(array $data)
    {
        $sys = $this->buildSystemProperties($data['sys']);

        return new DeletedAsset($sys);
    }

    /**
     * @param array $data
     *
     * @return DeletedContentType
     */
    private function buildDeletedContentType(array $data)
    {
        $sys = $this->buildSystemProperties($data['sys']);

        return new DeletedContentType($sys);
    }

    /**
     * @param  array $data
     *
     * @return DeletedEntry
     */
    private function buildDeletedEntry(array $data)
    {
        $sys = $this->buildSystemProperties($data['sys']);

        return new DeletedEntry($sys);
    }

    /**
     * @param  array $data
     *
     * @return ContentTypeField
     */
    private function buildContentTypeField(array $data)
    {
        return new ContentTypeField(
            $data['id'],
            $data['name'],
            $data['type'],
            isset($data['linkType']) ? $data['linkType'] : null,
            isset($data['items']) && isset($data['items']['type']) ? $data['items']['type'] : null,
            isset($data['items']) && isset($data['items']['linkType']) ? $data['items']['linkType'] : null,
            isset($data['required']) ? $data['required'] : false,
            isset($data['localized']) ? $data['localized'] : false,
            isset($data['disabled']) ? $data['disabled'] : false
        );
    }
}

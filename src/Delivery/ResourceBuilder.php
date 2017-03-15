<?php
/**
 * @copyright 2015-2017 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

use Contentful\Delivery\Client as DeliveryClient;
use Contentful\Delivery\Synchronization\DeletedAsset;
use Contentful\Delivery\Synchronization\DeletedEntry;
use Contentful\Location;
use Contentful\ResourceArray;

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
     * The ID of the space this ResourceBuilder is responsible for.
     *
     * @var string
     */
    private $spaceId;

    /**
     * ResourceBuilder constructor.
     *
     * @param Client        $client
     * @param InstanceCache $instanceCache
     * @param string        $spaceId
     */
    public function __construct(DeliveryClient $client, InstanceCache $instanceCache, $spaceId)
    {
        $this->client = $client;
        $this->instanceCache = $instanceCache;
        $this->spaceId = $spaceId;
    }

    /**
     * Build objects based on PHP classes from the raw JSON based objects.
     *
     * @param  array $data
     *
     * @return Asset|ContentType|DynamicEntry|Space|DeletedAsset|DeletedEntry|ResourceArray
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
     * @param  int         $depthCount
     *
     * @return Asset|ContentType|DynamicEntry|Space|DeletedAsset|DeletedEntry|ResourceArray
     */
    private function doBuildObjectsFromRawData(array $data, array $rawDataList = null, $depthCount = 0)
    {
        $type = $data['sys']['type'];

        switch ($type) {
            case 'Array':
                $itemList = $this->buildArrayDataList($data);

                return $this->buildArray($data, $itemList);
            case 'Asset':
                return $this->buildAsset($data);
            case 'ContentType':
                return $this->buildContentType($data);
            case 'Entry':
                return $this->buildEntry($data, $rawDataList, $depthCount);
            case 'Space':
                return $this->buildSpace($data);
            case 'DeletedAsset':
                return $this->buildDeletedAsset($data);
            case 'DeletedEntry':
                return $this->buildDeletedEntry($data);
            default:
                throw new \InvalidArgumentException('Unexpected type "' . $type . '"" while trying to build object.');
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

        if (isset($data['includes']['Entry'])) {
            foreach ($data['includes']['Entry'] as $item) {
                $entries[$item['sys']['id']] = $item;
            }
        }

        if (isset($data['includes']['Asset'])) {
            foreach ($data['includes']['Asset'] as $item) {
                $assets[$item['sys']['id']] = $item;
            }
        }

        foreach ($data['items'] as $item) {
            switch ($item['sys']['type']) {
                case 'Asset':
                    $assets[$item['sys']['id']] = $item;
                    break;
                case 'Entry':
                    $entries[$item['sys']['id']] = $item;
                    break;
                default:
                    // We ignore everything else since it's either cached elsewhere or won't need to be linked
            }
        }

        return [
            'asset' => $assets,
            'entry' => $entries
        ];
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
        $depthCount = 0;
        foreach ($data['items'] as $item) {
            $items[] = $this->doBuildObjectsFromRawData($item, $rawDataList, $depthCount);
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
        $files = array_map([$this, 'buildFile'], $this->normalizeFieldData($fields['file'], $locale));

        $asset = new Asset(
            isset($fields['title']) ? $this->normalizeFieldData($fields['title'], $locale) : null,
            isset($fields['description']) ? $this->normalizeFieldData($fields['description'], $locale) : null,
            $files,
            $sys
        );

        return $asset;
    }

    /**
     * Creates a File or a subclass thereof.
     *
     * @param  array $data
     *
     * @return File|ImageFile
     */
    private function buildFile(array $data)
    {
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
     * @param  int        $depthCount
     *
     * @return DynamicEntry
     */
    private function buildEntry(array $data, array $rawDataList = null, $depthCount = 0)
    {
        $sys = $this->buildSystemProperties($data['sys']);
        $locale = $sys->getLocale();
        $fields = [];
        if (isset($data['fields'])) {
            $fields = $this->buildFields($sys->getContentType(), $data['fields'], $locale, $rawDataList, $depthCount);
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
     * @param int         $depthCount
     *
     * @return array
     */
    private function buildFields(ContentType $contentType, array $fields, $locale, array $rawDataList = null, $depthCount = 0)
    {
        $result = [];
        foreach ($fields as $name => $fieldData) {
            $fieldConfig = $contentType->getField($name);
            if ($fieldConfig->isDisabled()) {
                continue;
            }
            $result[$name] = $this->buildField($fieldConfig, $this->normalizeFieldData($fieldData, $locale), $rawDataList, $depthCount);
        }
        return $result;
    }

    /**
     * @param ContentTypeField $fieldConfig
     * @param array            $fieldData
     * @param array|null       $rawDataList
     * @param int              $depthCount
     *
     * @return array
     */
    private function buildField(ContentTypeField $fieldConfig, array $fieldData, array $rawDataList = null, $depthCount = 0)
    {
        $result = [];
        foreach ($fieldData as $locale => $value) {
            $result[$locale] = $this->formatValue($fieldConfig, $value, $rawDataList, $depthCount);
        }

        return $result;
    }

    /**
     * Transforms values from the original JSON representation to an appropriate PHP representation.
     *
     * @param  ContentTypeField|string $fieldConfig Must be a ContentTypeField if the type is Array
     * @param  mixed                   $value
     * @param  array|null              $rawDataList
     * @param  int                     $depthCount
     *
     * @return array|Asset|DynamicEntry|Link|Location|\DateTimeImmutable
     */
    private function formatValue($fieldConfig, $value, array $rawDataList = null, $depthCount = 0)
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
                return $this->buildLink($value, $rawDataList, $depthCount);
            case 'Array':
                return array_map(function ($value) use ($fieldConfig, $rawDataList, $depthCount) {
                    return $this->formatValue($fieldConfig->getItemsType(), $value, $rawDataList, $depthCount);
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
     * @param  int        $depthCount
     *
     * @return Asset|DynamicEntry|Link
     *
     * @throws \InvalidArgumentException When encountering an unexpected link type. Only links to assets and entries are currently handled.
     */
    private function buildLink(array $data, array $rawDataList = null, $depthCount = 0)
    {
        $id = $data['sys']['id'];
        $type = $data['sys']['linkType'];

        if ($type === 'Asset') {
            if (isset($rawDataList['asset'][$id])) {
                return $this->buildAsset($rawDataList['asset'][$id]);
            }

            return new Link($id, $type);
        }
        if ($type === 'Entry') {
            if (isset($rawDataList['entry'][$id]) && $depthCount < 20) {
                $depthCount++;
                return $this->buildEntry($rawDataList['entry'][$id], $rawDataList, $depthCount);
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
            throw new SpaceMismatchException('This ResourceBuilder is responsible for the space "' . $this->spaceId . '" but was asked to build a resource for the space "' . $spaceId . '"."');
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
            throw new SpaceMismatchException('This ResourceBuilder is responsible for the space "' . $this->spaceId . '" but was asked to build a resource for the space "' . $data['sys']['id'] . '"."');
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

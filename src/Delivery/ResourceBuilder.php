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
     * @param  object $data
     *
     * @return Asset|ContentType|DynamicEntry|Space|DeletedAsset|DeletedEntry|ResourceArray
     */
    public function buildObjectsFromRawData($data)
    {
        return $this->doBuildObjectsFromRawData($data);
    }

    /**
     * Build objects based on PHP classes from the raw JSON based objects.
     *
     * @param  object      $data
     * @param  array|null  $rawDataList
     * @param  int         $depthCount
     *
     * @return Asset|ContentType|DynamicEntry|Space|DeletedAsset|DeletedEntry|ResourceArray
     */
    private function doBuildObjectsFromRawData($data, array $rawDataList = null, $depthCount = 0)
    {
        $type = $data->sys->type;

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
     * @param  object $data
     *
     * @return array
     */
    private function buildArrayDataList($data)
    {
        $entries = [];
        $assets = [];

        if (isset($data->includes)) {
            if (isset($data->includes->Entry)) {
                foreach ($data->includes->Entry as $item) {
                    $entries[$item->sys->id] = $item;
                }
            }

            if (isset($data->includes->Asset)) {
                foreach ($data->includes->Asset as $item) {
                    $assets[$item->sys->id] = $item;
                }
            }
        }

        foreach ($data->items as $item) {
            switch ($item->sys->type) {
                case 'Asset':
                    $assets[$item->sys->id] = $item;
                    break;
                case 'Entry':
                    $entries[$item->sys->id] = $item;
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
     * @param  object     $data
     * @param  array|null $rawDataList
     *
     * @return ResourceArray
     */
    private function buildArray($data, array $rawDataList = null)
    {
        $items = [];
        $depthCount = 0;
        foreach ($data->items as $item) {
            $items[] = $this->doBuildObjectsFromRawData($item, $rawDataList, $depthCount);
        }

        return new ResourceArray($items, $data->total, $data->limit, $data->skip);
    }

    /**
     * Build an Asset.
     *
     * @param  object $data
     *
     * @return Asset
     */
    private function buildAsset($data)
    {
        $fields = $data->fields;
        $files = (object) array_map([$this, 'buildFile'], (array) $fields->file);

        $asset = new Asset(
            isset($fields->title) ? $fields->title : null,
            isset($fields->description) ? $fields->description : null,
            $files,
            $this->buildSystemProperties($data->sys)
        );

        return $asset;
    }

    /**
     * Creates a File or a subclass thereof.
     *
     * @param  object $data
     *
     * @return File|ImageFile
     */
    private function buildFile($data)
    {
        $details = $data->details;
        if (isset($details->image)) {
            return new ImageFile(
                $data->fileName,
                $data->contentType,
                $data->url,
                $details->size,
                $details->image->width,
                $details->image->height
            );
        }

        return new File($data->fileName, $data->contentType, $data->url, $details->size);
    }

    /**
     * Creates a ContentType.
     *
     * @param  object $data
     *
     * @return ContentType|null
     */
    private function buildContentType($data)
    {
        if ($this->instanceCache->hasContentType($data->sys->id)) {
            return $this->instanceCache->getContentType($data->sys->id);
        }

        $sys = $this->buildSystemProperties($data->sys);
        $fields = array_map([$this, 'buildContentTypeField'], $data->fields);
        $displayField = isset($data->displayField) ? $data->displayField : null;
        $contentType = new ContentType(
            $data->name,
            isset($data->description) ? $data->description : null,
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
     * @param  object     $data
     * @param  array|null $rawDataList
     * @param  int        $depthCount
     *
     * @return DynamicEntry
     */
    private function buildEntry($data, array $rawDataList = null, $depthCount = 0)
    {
        $sys = $this->buildSystemProperties($data->sys);
        $fields = $this->buildFields($sys->getContentType(), $data->fields, $rawDataList, $depthCount);

        $entry = new DynamicEntry(
            $fields,
            $sys,
            $this->client
        );

        return $entry;
    }

    /**
     * @param ContentType $contentType
     * @param object      $fields
     * @param array|null  $rawDataList
     * @param int         $depthCount
     *
     * @return object
     */
    private function buildFields(ContentType $contentType, $fields, array $rawDataList = null, $depthCount = 0)
    {
        $result = new \stdClass();
        foreach ($fields as $name => $fieldData) {
            $fieldConfig = $contentType->getField($name);
            if ($fieldConfig->isDisabled()) {
                continue;
            }
            $result->$name = $this->buildField($fieldConfig, $fieldData, $rawDataList, $depthCount);
        }
        return $result;
    }

    /**
     * @param ContentTypeField $fieldConfig
     * @param object           $fieldData
     * @param array|null       $rawDataList
     * @param int              $depthCount
     *
     * @return object
     */
    private function buildField(ContentTypeField $fieldConfig, $fieldData, array $rawDataList = null, $depthCount = 0)
    {
        $result = new \stdClass;
        foreach ($fieldData as $locale => $value) {
            $result->$locale = $this->formatValue($fieldConfig, $value, $rawDataList, $depthCount);
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
                return new Location($value->lat, $value->lon);
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
     * @param  object     $data
     * @param  array|null $rawDataList
     * @param  int        $depthCount
     *
     * @return Asset|DynamicEntry|Link
     *
     * @throws \InvalidArgumentException When encountering an unexpected link type. Only links to assets and entries are currently handled.
     */
    private function buildLink($data, array $rawDataList = null, $depthCount = 0)
    {
        $id = $data->sys->id;
        $type = $data->sys->linkType;

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
     * @param  object $data
     *
     * @return Space|null
     *
     * @throws SpaceMismatchException When attempting to build a different Space than the one this ResourceBuilder is configured to handle.
     */
    private function buildSpace($data)
    {
        if ($data->sys->id !== $this->spaceId) {
            throw new SpaceMismatchException('This ResourceBuilder is responsible for the space "' . $this->spaceId . '" but was asked to build a resource for the space "' . $data->sys->id . '"."');
        }

        if ($this->instanceCache->hasSpace()) {
            return $this->instanceCache->getSpace();
        }

        $locales = [];
        foreach ($data->locales as $locale) {
            $locales[] = new Locale($locale->code, $locale->name, $locale->fallbackCode, $locale->default);
        }
        $sys = $this->buildSystemProperties($data->sys);
        $space = new Space($data->name, $locales, $sys);
        $this->instanceCache->setSpace($space);

        return $space;
    }

    /**
     * @param  object $sys
     *
     * @return SystemProperties
     */
    private function buildSystemProperties($sys)
    {
        return new SystemProperties(
            isset($sys->id) ? $sys->id : null,
            isset($sys->type) ? $sys->type : null,
            isset($sys->space) ? $this->getSpace($sys->space->sys->id) : null,
            isset($sys->contentType) ? $this->client->getContentType($sys->contentType->sys->id) : null,
            isset($sys->revision) ? $sys->revision : null,
            isset($sys->createdAt) ? new \DateTimeImmutable($sys->createdAt) : null,
            isset($sys->updatedAt) ? new \DateTimeImmutable($sys->updatedAt) : null,
            isset($sys->deletedAt) ? new \DateTimeImmutable($sys->deletedAt) : null
        );
    }

    /**
     * @param  object $data
     *
     * @return DeletedAsset
     */
    private function buildDeletedAsset($data)
    {
        $sys = $this->buildSystemProperties($data->sys);
        return new DeletedAsset($sys);
    }

    /**
     * @param  object $data
     *
     * @return DeletedEntry
     */
    private function buildDeletedEntry($data)
    {
        $sys = $this->buildSystemProperties($data->sys);
        return new DeletedEntry($sys);
    }

    /**
     * @param  object $data
     *
     * @return ContentTypeField
     */
    private function buildContentTypeField($data)
    {
        return new ContentTypeField(
            $data->id,
            $data->name,
            $data->type,
            isset($data->linkType) ? $data->linkType : null,
            isset($data->items) && isset($data->items->type) ? $data->items->type : null,
            isset($data->items) && isset($data->items->linkType) ? $data->items->linkType : null,
            isset($data->required) ? $data->required : false,
            isset($data->localized) ? $data->localized : false,
            isset($data->disabled) ? $data->disabled : false
        );
    }
}

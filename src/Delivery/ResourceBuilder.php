<?php
/**
 * @copyright 2015 Contentful GmbH
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
        $type = $data->sys->type;

        if ($type === 'Array' && isset($data->includes)) {
            $this->processIncludes($data->includes);
        }

        switch ($type) {
            case 'Array':
                return $this->buildArray($data);
            case 'Asset':
                return $this->buildAsset($data);
            case 'ContentType':
                return $this->buildContentType($data);
            case 'Entry':
                return $this->buildEntry($data);
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
     * Process the includes of an API response.
     *
     * @param object $includes
     */
    private function processIncludes($includes)
    {
        if (isset($includes->Asset)) {
            foreach ($includes->Asset as $asset) {
                $this->buildAsset($asset);
            }
        }
        if (isset($includes->Entry)) {
            foreach ($includes->Entry as $entry) {
                $this->buildEntry($entry);
            }
        }
    }

    /**
     * Build a ResourceArray.
     *
     * @param  object $data
     *
     * @return ResourceArray
     */
    private function buildArray($data)
    {
        $items = array_map([$this, 'buildObjectsFromRawData'], $data->items);
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
        if ($this->instanceCache->hasAsset($data->sys->id)) {
            return $this->instanceCache->getAsset($data->sys->id);
        }

        $fields = $data->fields;
        $files = (object) array_map([$this, 'buildFile'], (array) $fields->file);

        $asset = new Asset(
            $fields->title,
            isset($fields->description) ? $fields->description : null,
            $files,
            $this->buildSystemProperties($data->sys)
        );
        $this->instanceCache->addAsset($asset);

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
     * @param  object $data
     *
     * @return DynamicEntry
     */
    private function buildEntry($data)
    {
        if ($this->instanceCache->hasEntry($data->sys->id)) {
            return $this->instanceCache->getEntry($data->sys->id);
        }

        $sys = $this->buildSystemProperties($data->sys);
        $fields = $this->buildFields($sys->getContentType(), $data->fields);

        $entry = new DynamicEntry(
            $fields,
            $sys,
            $this->client
        );
        $this->instanceCache->addEntry($entry);

        return $entry;
    }

    /**
     * @param ContentType $contentType
     * @param object      $fields
     *
     * @return object
     */
    private function buildFields(ContentType $contentType, $fields)
    {
        $result = new \stdClass();
        foreach ($fields as $name => $fieldData) {
            $fieldConfig = $contentType->getField($name);
            if ($fieldConfig->isDisabled()) {
                continue;
            }
            $result->$name = $this->buildField($fieldConfig, $fieldData);
        }
        return $result;
    }

    /**
     * @param ContentTypeField $fieldConfig
     * @param object           $fieldData
     *
     * @return object
     */
    private function buildField(ContentTypeField $fieldConfig, $fieldData)
    {
        $result = new \stdClass;
        foreach($fieldData as $locale => $value) {
            $result->$locale = $this->formatValue($fieldConfig, $value);
        }

        return $result;
    }

    /**
     * Transforms values from the original JSON representation to an appropriate PHP representation.
     *
     * @param  ContentTypeField|string $fieldConfig Must be a ContentTypeField if the type is Array
     * @param  mixed $value
     *
     * @return array|Asset|DynamicEntry|Link|Location|\DateTimeImmutable
     */
    private function formatValue($fieldConfig, $value)
    {
        if ($fieldConfig instanceof ContentTypeField) {
            $type = $fieldConfig->getType();
        }
        else {
            $type = $fieldConfig;
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
                return $this->buildLink($value);
            case 'Array':
                return array_map(function ($value) use ($fieldConfig) {
                    return $this->formatValue($fieldConfig->getItemsType(), $value);
                }, $value);
            default:
                throw new \InvalidArgumentException('Unexpected field type "' . $type . '" encounterted while trying to format value.');
        }
    }

    /**
     * When an instance of the target already exists, it is returned. If not, a Link is created as placeholder.
     *
     * @param  object $data
     *
     * @return Asset|DynamicEntry|Link
     *
     * @throws \InvalidArgumentException When encountering an unexpected link type. Only links to assets and entries are currently handled.
     */
    private function buildLink($data)
    {
        $id = $data->sys->id;
        $type = $data->sys->linkType;

        if ($type === 'Asset') {
            if ($this->instanceCache->hasAsset($id)) {
                return $this->instanceCache->getAsset($id);
            }

            return new Link($id, $type);
        }
        if ($type === 'Entry') {
            if ($this->instanceCache->hasEntry($id)) {
                return $this->instanceCache->getEntry($id);
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
            $locales[] = new Locale($locale->code, $locale->name, $locale->default);
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

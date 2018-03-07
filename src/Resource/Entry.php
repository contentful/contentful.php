<?php

/**
 * This file is part of the contentful.php package.
 *
 * @copyright 2015-2018 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Resource;

use Contentful\Core\Api\DateTimeImmutable;
use Contentful\Core\Api\Link;
use Contentful\Core\Exception\NotFoundException;
use Contentful\Core\Resource\ResourceArray;
use Contentful\Delivery\Query;
use Contentful\Delivery\Resource\ContentType\Field;

class Entry extends LocalizedResource
{
    /**
     * @var array
     */
    protected $fields;

    /**
     * @var array
     */
    protected $resolvedLinks = [];

    /**
     * @return int|null
     */
    public function getRevision()
    {
        return $this->sys->getRevision();
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getUpdatedAt()
    {
        return $this->sys->getUpdatedAt();
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getCreatedAt()
    {
        return $this->sys->getCreatedAt();
    }

    /**
     * Returns the space this entry belongs to.
     *
     * @return Space
     */
    public function getSpace()
    {
        return $this->sys->getSpace();
    }

    /**
     * Returns the environment this entry belongs to.
     *
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->sys->getEnvironment();
    }

    /**
     * @return ContentType|null
     */
    public function getContentType()
    {
        return $this->sys->getContentType();
    }

    /**
     * Gets all entries that contain links to the current one.
     * You can provide a Query object in order to set parameters
     * such as locale, include, and sorting.
     *
     * @param Query|null $query
     *
     * @return ResourceArray
     */
    public function getReferences(Query $query = null)
    {
        $query = $query ?: new Query();
        $query->linksToEntry($this->getId());

        return $this->client->getEntries($query);
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (0 !== \mb_strpos($name, 'get')) {
            \trigger_error('Call to undefined method '.__CLASS__.'::'.$name.'()', E_USER_ERROR);
        }
        $locale = $this->getLocaleFromInput(isset($arguments[0]) ? $arguments[0] : null);

        $fieldName = \mb_substr($name, 3);
        $getId = false;

        $fieldConfig = $this->getFieldConfigForName($fieldName);
        // If the field name doesn't exist, that might be because we're looking for the ID of reference, try that next.
        if (null === $fieldConfig && 'Id' === \mb_substr($fieldName, -2)) {
            $fieldName = \mb_substr($fieldName, 0, -2);
            $fieldConfig = $this->getFieldConfigForName($fieldName);
            $getId = true;
        }

        if (null === $fieldConfig) {
            \trigger_error('Call to undefined method '.__CLASS__.'::'.$name.'()', E_USER_ERROR);
        }

        // Since Entry::getFieldForName() manipulates the field name let's make sure we got the correct one
        $fieldName = $fieldConfig->getId();

        if (!isset($this->fields[$fieldName])) {
            if ('Array' === $fieldConfig->getType()) {
                return [];
            }

            return null;
        }

        if ($getId && !('Link' === $fieldConfig->getType() || ('Array' === $fieldConfig->getType() && 'Link' === $fieldConfig->getItemsType()))) {
            \trigger_error('Call to undefined method '.__CLASS__.'::'.$name.'()', E_USER_ERROR);
        }

        $value = $this->fields[$fieldName];
        if (!$fieldConfig->isLocalized()) {
            if (!isset($value[$locale])) {
                $locale = $this->sys->getEnvironment()->getDefaultLocale()->getCode();
            }
        } else {
            $locale = $this->loopThroughFallbackChain($value, $locale, $this->sys->getEnvironment());

            // We've reach the end of the fallback chain and there's no value
            if (null === $locale) {
                return null;
            }
        }

        $result = $value[$locale];
        if ($getId && 'Link' === $fieldConfig->getType()) {
            return $result->getId();
        }

        if ($result instanceof Link) {
            return $this->resolveLinkWithCache($result);
        }

        if ('Array' === $fieldConfig->getType() && 'Link' === $fieldConfig->getItemsType()) {
            if ($getId) {
                return \array_map([$this, 'mapIdValues'], $result);
            }

            return \array_filter(\array_map([$this, 'mapValues'], $result), function ($value) {
                return !$value instanceof \Exception;
            });
        }

        return $result;
    }

    /**
     * Resolves a Link into an Entry or Asset.
     * Resolved links are cached local to the object.
     *
     * @param Link $link
     *
     * @return Asset|self|null
     */
    private function resolveLinkWithCache(Link $link)
    {
        $locale = $this->getLocale() ?: '*';
        $cacheId = $link->getLinkType().'-'.$link->getId().'-'.$locale;

        if (isset($this->resolvedLinks[$cacheId])) {
            return $this->resolvedLinks[$cacheId];
        }

        $resource = $this->client->resolveLink($link, $locale);
        $this->resolvedLinks[$cacheId] = $resource;
        $resource->setLocale($this->getLocale());

        return $resource;
    }

    /**
     * @param string $fieldName
     *
     * @return Field|null
     */
    private function getFieldConfigForName($fieldName)
    {
        // Let's try the lower case version first, it's the more common one
        $field = $this->getContentType()->getField(\lcfirst($fieldName));

        if (null !== $field) {
            return $field;
        }

        return $this->getContentType()->getField($fieldName);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private function mapValues($value)
    {
        if ($value instanceof Link) {
            try {
                return $this->resolveLinkWithCache($value);
            } catch (NotFoundException $e) {
                return $e;
            }
        }

        return $value;
    }

    /**
     * @param Link|Entry|Asset $value
     *
     * @return string
     */
    private function mapIdValues($value)
    {
        return $value->getId();
    }

    /**
     * @param mixed  $value
     * @param string $type
     * @param string $linkType
     *
     * @return mixed
     */
    private function formatSimpleValueForJson($value, $type, $linkType)
    {
        switch ($type) {
            case 'Symbol':
            case 'Text':
            case 'Integer':
            case 'Number':
            case 'Boolean':
            case 'Location':
            case 'Object':
            case 'Unknown':
                return $value;
            case 'Date':
                return (string) $value;
            case 'Link':
                return $value ? [
                    'sys' => [
                        'type' => 'Link',
                        'linkType' => $linkType,
                        'id' => $value->getId(),
                    ],
                ] : null;
            default:
                throw new \InvalidArgumentException(\sprintf(
                    'Unexpected field type "%s" encountered while trying to serialize to JSON.',
                    $type
                ));
        }
    }

    /**
     * @param mixed $value
     * @param Field $fieldConfig
     *
     * @return mixed
     */
    private function formatValueForJson($value, Field $fieldConfig)
    {
        $type = $fieldConfig->getType();

        if ('Array' === $type) {
            return \array_map(function ($value) use ($fieldConfig) {
                return $this->formatSimpleValueForJson($value, $fieldConfig->getItemsType(), $fieldConfig->getItemsLinkType());
            }, $value);
        }

        return $this->formatSimpleValueForJson($value, $type, $fieldConfig->getLinkType());
    }

    public function jsonSerialize()
    {
        $entryLocale = $this->sys->getLocale();

        $fields = new \stdClass();
        $contentType = $this->getContentType();
        foreach ($this->fields as $fieldName => $fieldData) {
            $fields->$fieldName = new \stdClass();
            $fieldConfig = $contentType->getField($fieldName);
            if ($entryLocale) {
                $fields->$fieldName = $this->formatValueForJson($fieldData[$entryLocale], $fieldConfig);
            } else {
                foreach ($fieldData as $locale => $data) {
                    $fields->$fieldName->$locale = $this->formatValueForJson($data, $fieldConfig);
                }
            }
        }

        return [
            'sys' => $this->sys,
            'fields' => $fields,
        ];
    }
}

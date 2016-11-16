<?php
/**
 * @copyright 2015-2016 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery;

use Contentful\ResourceNotFoundException;

class DynamicEntry extends LocalizedResource implements EntryInterface
{
    /**
     * @var object
     */
    private $fields;

    /**
     * @var SystemProperties
     */
    protected $sys;

    /**
     * @var Client
     */
    protected $client;

    /**
     * Entry constructor.
     *
     * @param object           $fields
     * @param SystemProperties $sys
     * @param Client|null      $client
     */
    public function __construct($fields, SystemProperties $sys, Client $client = null)
    {
        parent::__construct($sys->getSpace()->getLocales());

        $this->fields = $fields;
        $this->sys = $sys;
        $this->client = $client;
    }

    public function getId()
    {
        return $this->sys->getId();
    }

    public function getRevision()
    {
        return $this->sys->getRevision();
    }

    public function getUpdatedAt()
    {
        return $this->sys->getUpdatedAt();
    }

    public function getCreatedAt()
    {
        return $this->sys->getCreatedAt();
    }

    public function getSpace()
    {
        return $this->sys->getSpace();
    }

    public function getContentType()
    {
        return $this->sys->getContentType();
    }

    /**
     * @param  string $name
     * @param  array  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (0 !== strpos($name, 'get')) {
            trigger_error('Call to undefined method ' . __CLASS__ . '::' . $name . '()', E_USER_ERROR);
        }
        $client = $this->client;
        $locale = $this->getLocaleFromInput(isset($arguments[0]) ? $arguments[0] : null);

        $fieldName = lcfirst(substr($name, 3));
        $getId = false;

        $fieldConfig = $this->getContentType()->getField($fieldName);
        // If the field name doesn't exist, that might be because we're looking for the ID of reference, try that next.
        if ($fieldConfig === null && substr($fieldName, -2) === 'Id') {
            $fieldName = substr($fieldName, 0, -2);
            $fieldConfig = $this->getContentType()->getField($fieldName);
            $getId = true;
        }

        if ($fieldConfig === null || $fieldConfig->isDisabled()) {
            trigger_error('Call to undefined method ' . __CLASS__ . '::' . $name . '()', E_USER_ERROR);
        }

        if (!isset($this->fields->$fieldName)) {
            if ($fieldConfig->getType() === 'Array') {
                return [];
            }

            return null;
        }

        if ($getId && !($fieldConfig->getType() === 'Link' || ($fieldConfig->getType() === 'Array' && $fieldConfig->getItemsType() === 'Link'))) {
            trigger_error('Call to undefined method ' . __CLASS__ . '::' . $name . '()', E_USER_ERROR);
        }

        $value = $this->fields->$fieldName;
        if (!$fieldConfig->isLocalized()) {
            $locale = $this->getSpace()->getDefaultLocale()->getCode();
        } else {
            $loopCounter = 0;
            while (!isset($value->$locale)) {
                $locale = $this->getSpace()->getLocale($locale)->getFallbackCode();
                if ($locale === null) {
                    // We've reach the end of the fallback chain and there's no value
                    return null;
                }
                $loopCounter++;
                // The number is arbitrary
                if ($loopCounter > 128) {
                    throw new \RuntimeException('Possible endless loop when trying to walk the locale fallback chain.');
                }
            }
        }

        $result = $value->$locale;
        if ($getId && $fieldConfig->getType() === 'Link') {
            return $result->getId();
        }

        if ($result instanceof Link) {
            return $client->resolveLink($result);
        }

        if ($fieldConfig->getType() === 'Array' && $fieldConfig->getItemsType() === 'Link') {
            if ($getId) {
                return array_map([$this, 'mapIdValues'], $result);
            }

            return array_filter(array_map([$this, 'mapValues'], $result), function ($value) {
                return !$value instanceof \Exception;
            });
        }

        return $result;
    }

    /**
     * @param  mixed $value
     *
     * @return mixed
     */
    private function mapValues($value)
    {
        if ($value instanceof Link) {
            try {
                return $this->client->resolveLink($value);
            } catch (ResourceNotFoundException $e) {
                return $e;
            }
        }

        return $value;
    }

    /**
     * @param  Link|DynamicEntry|Asset $value
     *
     * @return string
     */
    private function mapIdValues($value)
    {
        return $value->getId();
    }

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
                return $value;
            case 'Date':
                return $this->formatDateForJson($value);
            case 'Link':
                return $value ? (object) [
                    'sys' => (object) [
                        'type' => 'Link',
                        'linkType' => $linkType,
                        'id' => $value->getId()
                    ]
                ] : null;
            default:
                throw new \InvalidArgumentException('Unexpected field type "' . $type . '" encountered while trying to serialze to JSON.');
        }
    }

    private function formatValueForJson($value, ContentTypeField $fieldConfig)
    {
        $type = $fieldConfig->getType();

        if ($type === 'Array') {
            return array_map(function ($value) use ($fieldConfig) {
                return $this->formatSimpleValueForJson($value, $fieldConfig->getItemsType(), $fieldConfig->getItemsLinkType());
            }, $value);
        }

        return $this->formatSimpleValueForJson($value, $type, $fieldConfig->getLinkType());
    }

    public function jsonSerialize()
    {
        $fields = new \stdClass;
        $contentType = $this->getContentType();
        foreach ($this->fields as $fieldName => $fieldData) {
            $fields->$fieldName = new \stdClass;
            $fieldConfig = $contentType->getField($fieldName);
            foreach ($fieldData as $locale => $data) {
                $fields->$fieldName->$locale = $this->formatValueForJson($data, $fieldConfig);
            }
        }

        return (object) [
            'sys' => $this->sys,
            'fields' => $fields
        ];
    }

    /**
     * Unfortunately PHP has no eeasy way to create a nice, ISO 8601 formatted date string with miliseconds and Z
     * as the time zone specifier. Thus this hack.
     *
     * @param  \DateTimeImmutable $dt
     *
     * @return string ISO 8601 formatted date
     */
    private function formatDateForJson(\DateTimeImmutable $dt)
    {
        $dt = $dt->setTimezone(new \DateTimeZone('Etc/UTC'));
        return $dt->format('Y-m-d\TH:i:s.') . str_pad(floor($dt->format('u')/1000), 3, '0', STR_PAD_LEFT) . 'Z';
    }
}

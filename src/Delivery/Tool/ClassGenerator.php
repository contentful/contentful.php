<?php
/**
 * @copyright 2015 Contentful GmbH
 * @license   MIT
 */

namespace Contentful\Delivery\Tool;

use Contentful\Delivery\ContentType;
use Contentful\Delivery\ContentTypeField;

/**
 * The ClassGenerator generates PHP classes for entries.
 *
 * ClassGenerator can easily be customized by overwriting some of the protected methods. Do note, that there is not backwards
 * compatibility promise for override any method but `getClassName`.
 *
 * @api
 */
class ClassGenerator
{
    protected $spaces = '    ';

    protected static $classTemplate =
        '<?php
<namespace>
<useStatement>
<className>
{
<classBody>
}
';

    /**
     * ClassGenerator constructor.
     *
     * Empty constructor provided for forward compatibility.
     *
     * @api
     */
    public function __construct()
    {
    }

    /**
     * Generate a class for entries of the provided ContentType.
     *
     * @param  ContentType  $contentType
     *
     * @return string
     *
     * @api
     */
    public function generateEntryClass(ContentType $contentType)
    {
        $placeHolders = [
            '<namespace>',
            '<useStatement>',
            '<className>',
            '<classBody>'
        ];
        $replacements = [
            '', //@todo
            $this->generateUseStatement($contentType),
            $this->generateClassName($contentType),
            $this->generateClassBody($contentType)
        ];

        $code = str_replace($placeHolders, $replacements, static::$classTemplate) . "\n";
        return str_replace('<spaces>', $this->spaces, $code);
    }

    /**
     * Generate the use statements.
     *
     * @param  ContentType $contentType
     *
     * @return string
     */
    protected function generateUseStatement(ContentType $contentType)
    {
        $lines = [
            'use Contentful\Delivery\DynamicEntry;',
            'use Contentful\Delivery\Client;',
            'use Contentful\Delivery\Locale;',
            'use Contentful\Delivery\SystemProperties;'
        ];

        foreach ($contentType->getFields() as $field) {
            if ($field->getType() === 'Link' || $field->getItemsType() === 'Link') {
                $lines[] = 'use Contentful\Delivery\Link;';
            }
        }

        return implode("\n", array_unique($lines)) . "\n";
    }

    /**
     * Generate the class declaration
     *
     * @param  ContentType $contentType
     *
     * @return string
     */
    protected function generateClassName(ContentType $contentType)
    {
        return 'class ' . $this->getClassName($contentType) . ' extends DynamicEntry';
    }

    /**
     * Generate the class body.
     *
     * @param  ContentType $contentType
     *
     * @return string
     */
    protected function generateClassBody(ContentType $contentType)
    {
        $properties = [];
        $methods = [];

        $fields = $contentType->getFields();
        $methods[] = $this->generateConstructor($contentType);
        foreach ($fields as $field) {
            if ($field->isDisabled()) {
                continue;
            }

            $properties[] = $this->generateProperty($field);
            $methods[] = $this->generateMethod($field);
            if ($field->getType() === 'Link') {
                $methods[] = $this->generateIdMethod($field);
            }
        }
        $methods[] = $this->generateJsonSerialize($contentType);

        return implode("\n\n", $properties) . "\n\n" .  implode("\n\n", $methods);
    }

    /**
     * Get the name of the property of the given field.
     *
     * @param  ContentTypeField $field
     *
     * @return string
     */
    protected function getPropertyName(ContentTypeField $field)
    {
        return lcfirst($field->getId());
    }

    /**
     * Get the docblock for the property of the given field.
     *
     * @param  ContentTypeField $field
     *
     * @return string
     */
    protected function getPropertyDocBlockType(ContentTypeField $field)
    {
        if ($field->isLocalized()) {
            return 'object';
        }

        return $this->getMethodDocBlockType($field);
    }

    /**
     * Get the docblock for the getter method for the given field.
     *
     * @param  ContentTypeField $field
     *
     * @return string
     */
    protected function getMethodDocBlockType(ContentTypeField $field)
    {
        $type = $field->getType();
        $result = '';

        $typeMap = [
            'Symbol' => 'string',
            'Text' => 'string',
            'Integer' => 'int',
            'Number' => 'float',
            'Boolean' => 'bool',
            'Date' => '\DateTimeImmutable',
            'Location' => '\Contentful\Location',
            'Object' => 'object|array'
        ];

        if (isset($typeMap[$type])) {
            $result = $typeMap[$type];
        } elseif ($type === 'Array') {
            $result = 'array';
        } elseif ($type === 'Link') {
            $linkType = $field->getLinkType();
            if ($linkType === 'Entry') {
                $result = '\Contentful\Delivery\DynamicEntry';
            } elseif ($linkType === 'Asset') {
                $result = '\Contentful\Delivery\Asset';
            } else {
                throw new \InvalidArgumentException('Unexpected link type "' . $linkType . '" encountered while trying to generate class.');
            }
        } else {
            throw new \InvalidArgumentException('Unexpected field type "' . $type . '" encountered while trying to generate class.');
        }

        if (!$field->isRequired()) {
            $result .= '|null';
        }

        return $result;
    }

    /**
     * Generate the property of the given field.
     *
     * @param  ContentTypeField $field
     *
     * @return string
     */
    protected function generateProperty(ContentTypeField $field)
    {
        $lines = [];
        $lines[] = '<spaces>/**';
        $lines[] = '<spaces> * @var ' . $this->getPropertyDocBlockType($field);
        $lines[] = '<spaces> */';
        $lines[] = '<spaces>private $' . $this->getPropertyName($field) . ';';

        return implode("\n", $lines);
    }

    /**
     * Generate the Constructor.
     *
     * @param  ContentType $contentType
     *
     * @return string
     */
    protected function generateConstructor(ContentType $contentType)
    {
        $defaultLocale = $contentType->getSpace()->getDefaultLocale()->getCode();

        $lines = [];
        $lines[] = '<spaces>public function __construct($fields, SystemProperties $sys, Client $client)';
        $lines[] = '<spaces>{';
        $lines[] = '<spaces><spaces>parent::__construct(null, $sys, $client);';
        $lines[] = '';
        foreach ($contentType->getFields() as $field) {
            if ($field->isDisabled()) {
                continue;
            }

            $line = '<spaces><spaces>$this->' . $this->getPropertyName($field) . ' = $fields->' . $field->getId();
            if (!$field->isLocalized()) {
                $line .= '->{\'' . $defaultLocale . '\'}';
            }

            $line .= ';';

            $lines[] = $line;
        }
        $lines[] = '<spaces>}';

        return implode("\n", $lines);
    }

    /**
     * Generate the jsonSerialize function.
     *
     * @param  ContentType $contentType
     *
     * @return string
     */
    protected function generateJsonSerialize(ContentType $contentType)
    {
        $defaultLocale = $contentType->getSpace()->getDefaultLocale()->getCode();
        $fields = array_filter($contentType->getFields(), function ($field) {
            return !$field->isDisabled();
        });
        $fieldCount = count($fields);
        $i = 0;

        $lines = [];
        $lines[] = '<spaces>/**';
        $lines[] = '<spaces> * @return object';
        $lines[] = '<spaces> */';
        $lines[] = '<spaces>public function jsonSerialize()';
        $lines[] = '<spaces>{';
        $lines[] = '<spaces><spaces>$fields = (object) [';
        foreach ($fields as $field) {
            $i++;
            // We'll handle localized links in a second loop as they need a bit more logic
            if ($field->isLocalized()) {
                if ($this->isSimpleType($field->getType()) || ($field->getType() === 'Array' && $this->isSimpleType($field->getItemsType()))) {
                    $lines[] = '<spaces><spaces><spaces>\'' . $field->getId() .  '\' => $this->' . $this->getPropertyName($field) . ($i !== $fieldCount ? ',' : '');
                } else {
                    $lines[] = '<spaces><spaces><spaces>\'' . $field->getId() . '\' => new \stdClass' . ($i !== $fieldCount ? ',' : '');
                }

                continue;
            }

            $lines[] = '<spaces><spaces><spaces>\'' . $field->getId() . '\' => [';
            if ($this->isSimpleType($field->getType()) || ($field->getType() === 'Array' && $this->isSimpleType($field->getItemsType()))) {
                $lines[] = '<spaces><spaces><spaces><spaces>\'' . $defaultLocale . '\' => $this->' . $this->getPropertyName($field);
            } elseif ($field->getType() === 'Link') {
                $lines[] = '<spaces><spaces><spaces><spaces>\'' . $defaultLocale . '\' => (object) [';
                $lines[] = '<spaces><spaces><spaces><spaces><spaces>\'sys\' => (object) [';
                $lines[] = '<spaces><spaces><spaces><spaces><spaces><spaces>\'type\' => \'Link\',';
                $lines[] = '<spaces><spaces><spaces><spaces><spaces><spaces>\'linkType\' => \'' . $field->getLinkType() . '\',';
                $lines[] = '<spaces><spaces><spaces><spaces><spaces><spaces>\'id\' => $this->' . $this->getPropertyName($field) . '->getId()';
                $lines[] = '<spaces><spaces><spaces><spaces><spaces>]';
                $lines[] = '<spaces><spaces><spaces><spaces>]';
            } elseif ($field->getType() === 'Array' && $field->getItemsType() === 'Link') {
                $lines[] = '<spaces><spaces><spaces><spaces>\'' . $defaultLocale . '\' => array_map(function ($value) {';
                $lines[] = '<spaces><spaces><spaces><spaces><spaces>return (object) [';
                $lines[] = '<spaces><spaces><spaces><spaces><spaces><spaces>\'sys\' => (object) [';
                $lines[] = '<spaces><spaces><spaces><spaces><spaces><spaces><spaces>\'type\' => \'Link\',';
                $lines[] = '<spaces><spaces><spaces><spaces><spaces><spaces><spaces>\'linkType\' => \'' . $field->getItemsLinkType() . '\',';
                $lines[] = '<spaces><spaces><spaces><spaces><spaces><spaces><spaces>\'id\' => $this->' . $this->getPropertyName($field) . '->getId()';
                $lines[] = '<spaces><spaces><spaces><spaces><spaces><spaces>]';
                $lines[] = '<spaces><spaces><spaces><spaces><spaces>];';
                $lines[] = '<spaces><spaces><spaces><spaces>}, $this->' . $this->getPropertyName($field) . ');';
            } else {
                throw new \RuntimeException('Unexpected type "' . $field->getType() . '" while generating classes"');
            }
            $lines[] = '<spaces><spaces><spaces>]' . ($i !== $fieldCount ? ',' : '');
        }
        $lines[] = '<spaces><spaces>];';

        foreach ($fields as $field) {
            if (!$field->isLocalized() || ($field->getType() !== 'Link' || !($field->getType() === 'Array' && $field->getItemsType() === 'Link'))) {
                continue;
            }

            $lines[] = '<spaces><spaces>foreach ($this->' . $this->getPropertyName($field) . ' as $locale => $data) {';
            if ($field->getType() === 'Link') {
                $lines[] = '<spaces><spaces><spaces>$fields->' . $field->getId() . '->$locale = (object) [';
                $lines[] = '<spaces><spaces><spaces><spaces>\'sys\' => (object) [';
                $lines[] = '<spaces><spaces><spaces><spaces><spaces>\'type\' => \'Link\',';
                $lines[] = '<spaces><spaces><spaces><spaces><spaces>\'linkType\' => \'' . $field->getLinkType() . '\',';
                $lines[] = '<spaces><spaces><spaces><spaces><spaces>\'id\' => $data->getId()';
                $lines[] = '<spaces><spaces><spaces><spaces>]';
                $lines[] = '<spaces><spaces><spaces>]';
            } elseif ($field->getType() === 'Array') {
                $lines[] = '<spaces><spaces><spaces>$fields->' . $field->getId() . '->$locale = array_map(function ($value) {';
                $lines[] = '<spaces><spaces><spaces><spaces>return (object) [';
                $lines[] = '<spaces><spaces><spaces><spaces><spaces>\'sys\' => (object) [';
                $lines[] = '<spaces><spaces><spaces><spaces><spaces><spaces>\'type\' => \'Link\',';
                $lines[] = '<spaces><spaces><spaces><spaces><spaces><spaces>\'linkType\' => \'' . $field->getItemsLinkType() . '\',';
                $lines[] = '<spaces><spaces><spaces><spaces><spaces><spaces>\'id\' => $this->' . $this->getPropertyName($field) . '->getId()';
                $lines[] = '<spaces><spaces><spaces><spaces><spaces>]';
                $lines[] = '<spaces><spaces><spaces><spaces>];';
                $lines[] = '<spaces><spaces><spaces>}, $data);';
            }
            $lines[] = '<spaces><spaces>}';
        }

        $lines[] = '';
        $lines[] = '<spaces><spaces>return (object) [';
        $lines[] = '<spaces><spaces><spaces>\'sys\' => $this->sys,';
        $lines[] = '<spaces><spaces><spaces>\'fields\' => $fields';
        $lines[] = '<spaces><spaces>];';
        $lines[] = '<spaces>}';

        return implode("\n", $lines);
    }

    /**
     * Returns true if the type does not need any further processing.
     *
     * @param  string $type
     *
     * @return bool
     */
    protected function isSimpleType($type)
    {
        return $type === 'Symbol' || $type === 'Text' || $type === 'Integer' || $type === 'Integer' || $type === 'Number' || $type === 'Boolean' || $type === 'Date' || $type === 'Location' || $type === 'Object';
    }

    /**
     * Generate the getter method for the field.
     *
     * @param ContentTypeField $field
     *
     * @return string
     */
    protected function generateMethod(ContentTypeField $field)
    {
        $parameters = $field->isLocalized() ? '$locale = null' : '';

        if ($field->getType() === 'Link') {
            $body = $this->generateLinkMethodBody($field);
        } elseif ($field->getType() === 'Array') {
            $body = $this->generateArrayMethodBody($field);
        } else {
            $body = $this->generateSimpleMethodBody($field);
        }

        $lines = [];
        $lines[] = '<spaces>/**';
        if ($field->isLocalized()) {
            $lines[] = '<spaces> * @param  Locale|string|null $locale';
            $lines[] = '<spaces> *';
        }
        $lines[] = '<spaces> * @return ' . $this->getMethodDocBlockType($field);
        $lines[] = '<spaces> */';
        $lines[] = '<spaces>public function get' . ucfirst($field->getId()) . '(' . $parameters . ')';
        $lines[] = '<spaces>{';
        $lines[] = $body;
        $lines[] = '<spaces>}';

        return implode("\n", $lines);
    }

    /**
     * Generate the getter method body in simple cases.
     *
     * @param  ContentTypeField $field
     *
     * @return string
     */
    protected function generateSimpleMethodBody(ContentTypeField $field)
    {
        $lines = [];
        if ($field->isLocalized()) {
            $lines[] = '<spaces><spaces>$locale = $this->getLocaleFromInput($locale);';
            $lines[] = '';
            $lines[] = '<spaces><spaces>return $this->' . $this->getPropertyName($field) . '->$locale;';
        } else {
            $lines[] = '<spaces><spaces>return $this->' . $this->getPropertyName($field) . ';';
        }

        return implode("\n", $lines);
    }

    /**
     * Generate the getter method body for links.
     *
     * @param  ContentTypeField $field
     *
     * @return string
     */
    protected function generateLinkMethodBody(ContentTypeField $field)
    {
        $lines = [];
        if ($field->isLocalized()) {
            $lines[] = '<spaces><spaces>$locale = $this->getLocaleFromInput($locale);';
            $lines[] = '';
            $lines[] = '<spaces><spaces>$result = $this->' . $this->getPropertyName($field) . '->$locale;';
        } else {
            $lines[] = '<spaces><spaces>$result = $this->' . $this->getPropertyName($field) . ';';
        }
        $lines[] = '<spaces><spaces>if ($result instanceof Link) {';
        $lines[] = '<spaces><spaces><spaces>return $this->client->resolveLink($result);';
        $lines[] = '<spaces><spaces>}';
        $lines[] = '';
        $lines[] = '<spaces><spaces>return $result;';

        return implode("\n", $lines);
    }

    /**
     * Generate the getter method body for arrays.
     *
     * @param ContentTypeField $field
     *
     * @return string
     */
    protected function generateArrayMethodBody(ContentTypeField $field)
    {
        if ($field->getItemsType() !== 'Link') {
            return $this->generateSimpleMethodBody($field);
        }

        $lines = [
            '<spaces><spaces>$client = $this->client;'
        ];
        if ($field->isLocalized()) {
            $lines[] = '<spaces><spaces>$locale = $this->getLocaleFromInput($locale);';
            $lines[] = '';
            $lines[] = '<spaces><spaces>$result = $this->' . $this->getPropertyName($field) . '->$locale;';
        } else {
            $lines[] = '<spaces><spaces>$result = $this->' . $this->getPropertyName($field) . ';';
        }

        $lines[] = '<spaces><spaces>return array_map(function ($value) use ($client) {';
        $lines[] = '<spaces><spaces><spaces>if ($value instanceof Link) {';
        $lines[] = '<spaces><spaces><spaces><spaces>return $client->resolveLink($value);';
        $lines[] = '<spaces><spaces><spaces>}';
        $lines[] = '';
        $lines[] = '<spaces><spaces><spaces>return $value;';
        $lines[] = '<spaces><spaces>}, $result);';

        return implode("\n", $lines);
    }

    /**
     * Fields that reference links (or arrays of links) also get a method to get just the ID.
     *
     * @param  ContentTypeField $field
     *
     * @return string
     */
    protected function generateIdMethod(ContentTypeField $field)
    {
        $parameters = $field->isLocalized() ? '$locale = null' : '';

        $lines = [
            '<spaces>/**'
        ];
        if ($field->isLocalized()) {
            $lines[] = '<spaces> * @param  Locale|string|null $locale';
            $lines[] = '<spaces> *';
        }

        if ($field->getType() === 'Link') {
            $lines[] = '<spaces> * @return string';
            $lines[] = '<spaces> */';
            $lines[] = '<spaces>public function get' . ucfirst($field->getId()) . 'Id(' . $parameters . ')';
            $lines[] = '<spaces>{';

            if ($field->isLocalized()) {
                $lines[] = '<spaces><spaces>$locale = $this->getLocaleFromInput($locale);';
                $lines[] = '';
                $lines[] = '<spaces><spaces>return $this->' . $this->getPropertyName($field) . '->$locale->getId();';
            } else {
                $lines[] = '<spaces><spaces>return $this->' . $this->getPropertyName($field) . '->getId();';
            }
        }
        if (false && $field->getType() === 'Array' && $field->getItemsType() === 'Link') {
            $lines[] = '<spaces> * @return string[]';
            $lines[] = '<spaces> */';
            $lines[] = '<spaces>public function get' . ucfirst($field->getId()) . 'Id(' . $parameters . ')';
            $lines[] = '<spaces>{';
            if ($field->isLocalized()) {
                $lines[] = '<spaces><spaces>$locale = $this->getLocaleFromInput($locale);';
                $lines[] = '';
                $lines[] = '<spaces><spaces>$result = $this->' . $this->getPropertyName($field) . '->$locale->getId();';
            } else {
                $lines[] = '<spaces><spaces>$result = $this->' . $this->getPropertyName($field) . '->getId();';
            }
            $lines[] = '<spaces><spaces>return array_map(function ($value) use ($client) {';
            $lines[] = '<spaces><spaces><spaces>return $value->getId();';
            $lines[] = '<spaces><spaces>}, $result);';
        }

        $lines[] = '<spaces>}';

        return implode("\n", $lines);
    }

    /**
     * Get the class name for and entry of the provided ContentType.
     *
     * @param ContentType $contentType
     *
     * @return string
     *
     * @api
     */
    public function getClassName(ContentType $contentType)
    {
        return 'Entry' . ucfirst($contentType->getId());
    }
}

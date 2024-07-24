<?php

/**
 * This file is part of the contentful/contentful package.
 *
 * @copyright 2015-2024 Contentful GmbH
 * @license   MIT
 */

declare(strict_types=1);

namespace Contentful\Delivery\Resource;

use Contentful\Core\Api\Link;
use Contentful\Core\Resource\EntryInterface;
use Contentful\Core\Resource\ResourceArray;
use Contentful\Delivery\Client\ClientInterface;
use Contentful\Delivery\Query;
use Contentful\Delivery\Resource\ContentType\Field;
use Contentful\Delivery\SystemProperties\Component\TagTrait;
use Contentful\Delivery\SystemProperties\Entry as SystemProperties;

class Entry extends LocalizedResource implements EntryInterface, \ArrayAccess
{
    use TagTrait {
        getTags as getContentfulTags;
        initTags as initContentfulTags;
    }

    /**
     * @var array
     */
    protected $fields;

    /**
     * @var SystemProperties
     */
    protected $sys;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var bool
     */
    protected $disableTags = false;

    public function getSystemProperties(): SystemProperties
    {
        return $this->sys;
    }

    /**
     * Returns the space this entry belongs to.
     */
    public function getSpace(): Space
    {
        return $this->sys->getSpace();
    }

    /**
     * Returns the environment this entry belongs to.
     */
    public function getEnvironment(): Environment
    {
        return $this->sys->getEnvironment();
    }

    public function getContentType(): ContentType
    {
        return $this->sys->getContentType();
    }

    public function __call(string $name, array $arguments)
    {
        if (0 === mb_strpos($name, 'has')) {
            $field = $this->sys->getContentType()->getField($name, true);

            // Only works if the "has" is "magic", i.e.
            // the field is not actually called hasSomething.
            if (!$field) {
                return $this->has(
                    mb_substr($name, 3),
                    $this->getLocaleFromInput($arguments[0] ?? null),
                    $arguments[1] ?? true
                );
            }
        }

        // Some templating languages might end up trying to access a field
        // using a method-like syntax "$entry->field()".
        // Even though it's not the suggested approach, we allow that syntax
        // for maximum compatibility purposes.
        if (0 === mb_strpos($name, 'get')) {
            $name = mb_substr($name, 3);
        }

        $locale = $this->getLocaleFromInput($arguments[0] ?? null);

        return $this->get(
            $name,
            $locale,
            (bool) ($arguments[1] ?? true)
        );
    }

    /**
     * Shortcut for accessing fields using $entry->fieldName.
     * It will use the locale currently defined.
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    public function offsetGet(mixed $name): mixed
    {
        return $this->get($name);
    }

    public function offsetExists(mixed $name): bool
    {
        return $this->has($name);
    }

    public function offsetSet(mixed $name, mixed $value): void
    {
        throw new \LogicException('Entry class does not support setting fields.');
    }

    public function offsetUnset(mixed $name): void
    {
        throw new \LogicException('Entry class does not support unsetting fields.');
    }

    /**
     * Checks whether the current entry has a field with a certain ID.
     */
    public function has(string $name, ?string $locale = null, bool $checkLinksAreResolved = true): bool
    {
        $field = $this->sys->getContentType()->getField($name, true);

        if (!$field) {
            return false;
        }

        if (!\array_key_exists($field->getId(), $this->fields)) {
            return false;
        }

        try {
            $result = $this->getUnresolvedField($field, $locale);
            if ($checkLinksAreResolved) {
                $this->resolveFieldLinks($result, $locale);
            }
        } catch (\Exception $exception) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function __isset(string $name)
    {
        return $this->has($name);
    }

    /**
     * Returns all fields of the current entry, with some optimizations applied.
     * Links are resolved by default. If you want to get raw link objects rather than
     * complete resources, set the $resolveLinks parameter to false.
     *
     * @param string|null $locale                            the locale to access the fields with
     * @param bool|int    $resolveLinkDepth                  Whether to resolve links and, if so, how deep.
     *                                                       Set to false or zero to disable link resolution.
     *                                                       You can also set it to true, which is treated as 1 for backwards-compatible reasons.
     * @param bool        $ignoreLocaleForNonLocalizedFields Whether to access non-localized fields using the given locale.
     *                                                       Unless this parameter is set, doing so will result in an exception. This behaviour is breaking to older
     *                                                       versions and therefore not default.
     */
    public function all(?string $locale = null, bool|int $resolveLinkDepth = 1, bool $ignoreLocaleForNonLocalizedFields = false): array
    {
        if (false === $resolveLinkDepth) {
            $resolveLinkDepth = 0;
        } elseif (true === $resolveLinkDepth) {
            $resolveLinkDepth = 1;
        }

        $values = [];
        foreach ($this->getContentType()->getFields() as $field) {
            $result = null;
            if ($ignoreLocaleForNonLocalizedFields && !$field->isLocalized()) {
                // If this field is non-localized, accessing it with a locale would result in an error. Therefore, we
                // need to access if without any locale, to fall back to it's only value.
                $result = $this->getUnresolvedField($field);
            } else {
                $result = $this->getUnresolvedField($field, $locale);
            }

            if ($resolveLinkDepth > 0) {
                $result = $this->resolveFieldLinks($result, $locale);
                if ($resolveLinkDepth > 1) {
                    // The first layer of links is the links in this Entry, so we'll only resolve the links in our links
                    // if the link depth is over one.
                    $result = $this->resolveLinksIfEntryOrEntryArray($result, $resolveLinkDepth - 1, $ignoreLocaleForNonLocalizedFields, $locale);
                }
            }
            $values[$field->getId()] = $result;
        }

        return $values;
    }

    /**
     * Returns true if the field contains locale dependent content.
     *
     * @param string $name the name of the field
     *
     * @return bool whether the given field is localized
     */
    public function isFieldLocalized(string $name): bool
    {
        $field = $this->sys->getContentType()->getField($name, true);

        if ($field) {
            return $field->isLocalized();
        }

        throw new \InvalidArgumentException(sprintf('Trying to access non existent field "%s" on an entry with content type "%s" ("%s").', $name, $this->sys->getContentType()->getName(), $this->sys->getContentType()->getSystemProperties()->getId()));
    }

    /**
     * Returns the value of a field using the given locale.
     * It will also resolve links. If you want to access the ID of a link
     * or of an array of links, simply append "Id" to the end of the
     * $name parameter.
     *
     * ```
     * $author = $entry->get('author');
     * $id = $entry->get('authorId');
     * ```
     *
     * @param bool|int $resolveLinkDepth Whether to resolve links and, if so, how deep.
     *                                   Set to false or zero to disable link resolution.
     *                                   You can also set it to true, which is treated as 1 for backwards-compatible reasons.
     */
    public function get(string $name, ?string $locale = null, bool|int $resolveLinkDepth = 1)
    {
        if (false === $resolveLinkDepth) {
            $resolveLinkDepth = 0;
        } elseif (true === $resolveLinkDepth) {
            $resolveLinkDepth = 1;
        }

        $field = $this->sys->getContentType()->getField($name, true);
        if ($field) {
            $result = $this->getUnresolvedField($field, $locale);

            if ($resolveLinkDepth > 0) {
                $result = $this->resolveFieldLinks($result, $locale);
                if ($resolveLinkDepth > 1) {
                    // The first layer of links is the links in this Entry, so we'll only resolve the links in our links
                    // if the link depth is over one.
                    $result = $this->resolveLinksIfEntryOrEntryArray($result, $resolveLinkDepth - 1, true, $locale);
                }
            }

            return $result;
        }

        // If no clean match was found using the provided field name,
        // let's attempt to see if we're fetching an ID of a link or array of links.
        $value = $this->getFieldWithId($name, $locale);
        if (null !== $value) {
            return $value;
        }

        throw new \InvalidArgumentException(sprintf('Trying to access non existent field "%s" on an entry with content type "%s" ("%s").', $name, $this->sys->getContentType()->getName(), $this->sys->getContentType()->getSystemProperties()->getId()));
    }

    /**
     * Attempts to fetch a value given the current configuration.
     * It will return the raw field value,
     * without applying any transformation to it.
     *
     * @param Field       $field  the field to access
     * @param string|null $locale The locale to access the field with. Falls back to the default locale.
     */
    private function getUnresolvedField(Field $field, ?string $locale = null, bool $ignoreLocaleOnNonLocalizedFields = false)
    {
        // The field is not currently available on this resource,
        // but it exists in the content type, so we return an appropriate
        // default value.
        if (!isset($this->fields[$field->getId()])) {
            return 'Array' === $field->getType() ? [] : null;
        }

        $value = $this->fields[$field->getId()];

        // This also checks two things:
        // * the compatibility of the given locale with the one present in sys.locale
        // * the existence of the given locale among those available in the environment
        // If we're trying to access locale X and the entry was built with locale Y,
        // or the environment does not support such locale, an exception will be thrown.
        // To allow accessing multiple locales on a single entry, fetching it
        // with locale=* is required.
        $locale = $this->getLocaleFromInput($locale);

        if (\array_key_exists($locale, $value)) {
            return $value[$locale];
        }

        // If a field is not localized, it means that there are no values besides
        // $value[$defaultLocale], so because that check has already happened, we know
        // we're trying to access an invalid locale which is not correctly set.
        if (!$field->isLocalized()) {
            throw new \InvalidArgumentException(sprintf('Trying to access the non-localized field "%s" on content type "%s" using the non-default locale "%s".', $field->getName(), $this->sys->getContentType()->getName(), $locale));
        }

        // If we reach this point, it means:
        // * the field is localized
        // * we're trying to get a non-default locale
        // * the entry was not built using a specific locale (i.e. all locales for a field are available)
        // Therefore, we can inspect the fallback chain for a suitable locale.
        $locale = $this->walkFallbackChain($value, $locale, $this->sys->getEnvironment());

        if ($locale) {
            return $value[$locale];
        }

        return 'Array' === $field->getType() ? [] : null;
    }

    /**
     * Resolves all link fields recursively.
     *
     * @param int  $depth                             max iteration depth to resolve links into
     * @param bool $ignoreLocaleForNonLocalizedFields whether to access non-localized fields using the given locale
     */
    private function resolveAllFieldLinks(int $depth, bool $ignoreLocaleForNonLocalizedFields, ?string $locale = null)
    {
        if ($depth < 1) {
            return;
        }
        --$depth;

        foreach ($this->getContentType()->getFields() as $field) {
            $result = null;
            if ($ignoreLocaleForNonLocalizedFields && !$field->isLocalized()) {
                // If this field is non-localized, accessing it with a locale would result in an error. Therefore, we
                // need to access if without any locale, to fall back to it's only value.
                $result = $this->getUnresolvedField($field);
            } else {
                $result = $this->getUnresolvedField($field, $locale);
            }

            $result = $this->resolveFieldLinks($result, $locale);
            $this->resolveLinksIfEntryOrEntryArray($result, $depth, $ignoreLocaleForNonLocalizedFields, $locale);
        }
    }

    /**
     * Resolves all links on an item if it's an instance of Entry or an array containing Entry.
     *
     * @param mixed $item                              the item in question
     * @param int   $depth                             how deep to recurse
     * @param bool  $ignoreLocaleForNonLocalizedFields whether to access non-localized fields using the given locale
     *
     * @return mixed the item, with resolved links if so
     */
    private function resolveLinksIfEntryOrEntryArray(mixed $item, int $depth, bool $ignoreLocaleForNonLocalizedFields, ?string $locale): mixed
    {
        if ($item instanceof self) {
            $item->resolveAllFieldLinks($depth, $ignoreLocaleForNonLocalizedFields, $locale);
        } elseif (\is_array($item)) {
            foreach ($item as $element) {
                if ($element instanceof self) {
                    $element->resolveAllFieldLinks($depth, $ignoreLocaleForNonLocalizedFields, $locale);
                }
            }
        }

        return $item;
    }

    /**
     * Given a field value, this method will resolve links
     * if it's a Link object or an array of links.
     */
    private function resolveFieldLinks($field, ?string $locale = null)
    {
        // If no locale is set, to resolve links we use either the special "*" locale,
        // or the default one, depending on whether this entry was built using a locale or not
        if (null === $locale) {
            $locale = null === $this->sys->getLocale()
                ? '*'
                : $this->getLocale();
        }

        if ($field instanceof Link) {
            return $this->client->resolveLink($field, $locale);
        }

        if (\is_array($field) && isset($field[0]) && $field[0] instanceof Link) {
            return $this->client->resolveLinkCollection($field, $locale);
        }

        return $field;
    }

    /**
     * Checks whether the given $name parameter corresponds to an attempt
     * of fetching the ID of a link (or array of links).
     *
     * @return string|string[]|null Returns null if $name is not a valid field ID string
     */
    private function getFieldWithId(string $name, ?string $locale = null)
    {
        if ('Id' !== mb_substr($name, -2)) {
            return null;
        }

        $field = $this->sys->getContentType()->getField(mb_substr($name, 0, -2), true);
        if (!$field) {
            return null;
        }

        if ('Link' !== $field->getType() && ('Array' !== $field->getType() || 'Link' !== $field->getItemsType())) {
            return null;
        }

        $value = $this->getUnresolvedField($field, $locale);
        if ($value instanceof Link) {
            return $value->getId();
        }

        return array_map(function (Link $link) {
            return $link->getId();
        }, $value);
    }

    /**
     * Gets all entries that contain links to the current one.
     * You can provide a Query object in order to set parameters
     * such as locale, include, and sorting.
     */
    public function getReferences(?Query $query = null): ResourceArray
    {
        $query = $query ?: new Query();
        $query->linksToEntry($this->getId());

        return $this->client->getEntries($query);
    }

    /**
     * Initialize an entries tags.
     *
     * @param Tag[] $tags the tags to set
     */
    public function initTags(array $tags)
    {
        $this->initContentfulTags($tags);
        // We need to check that the content type does not have a "tags" field, since we would otherwise shadow the
        // getter method used for that and possibly break existing code. Therefore, if we have that field, we emit a
        // warning and let the user fall back to the alternate method.
        if ($this->has('tags')) {
            error_log(
                "Warning: Content type '".
                $this->getType().
                "' has a field 'tags', which shadows Contentful tags. ".
                'You can call Entry::getContentfulTags() or change the field name to access them.'
            );
            $this->disableTags = true;
        } else {
            $this->disableTags = false;
        }
    }

    /**
     * Get all tags of the entry.
     */
    public function getTags(): array
    {
        if ($this->disableTags) {
            return $this->get('Tags');
        }

        return $this->getContentfulTags();
    }

    public function jsonSerialize(): array
    {
        $locale = $this->sys->getLocale();

        $fields = new \stdClass();
        foreach ($this->fields as $name => $value) {
            $fields->$name = $locale ? $value[$locale] : $value;
        }

        return [
            'sys' => $this->sys,
            'fields' => $fields,
        ];
    }
}
